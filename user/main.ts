import { SetupSaveCancelButtons } from "../assets/sharedScripts.js"
import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js"
import { HTMLFormInputElement, SendToast } from "../formWebScripts/js/formScript.js"
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js"
const urlSearchParams = new URLSearchParams(window.location.search)
localStorage.setItem("formLanguage", "cs")
const dialogManager = new FormDialogManager()
SetupSaveCancelButtons(dialogManager, "userInfo", ".", "./user.php", "-")
for (const element of document.getElementsByClassName("attendantInfo")) {
    SetupSaveCancelButtons(dialogManager, element as HTMLElement, ".", "./attendant.php", element.getAttribute("attendant") as string)
}
for (const element of document.getElementsByClassName("companyInfo")) {
    SetupSaveCancelButtons(dialogManager, element as HTMLElement, ".", "./company.php", element.getAttribute("company") as string)
}


//Make move email button work
document.getElementById("btnChangeEmail")?.addEventListener("click", async () => {
    //Ask for email
    const email = await dialogManager.OpenPrompt<null | string>("Přenos na jiný účet", "Zadejte nový Email, kterým se budete přihlašovat do aplikace. Starý přístup zanikne.", null, "email", "Email", true, true)
    if (email == null) {
        SendToast("Přenos účtu na jiný Email zrušen!", "Akce byla zrušena úspěšně.", "ok")
        return
    }

    //Send POST
    const progress = dialogManager.ShowProgress("Přenos účtu na jiný Email", "Probíhá vytváření požadavku, čekejte prosím...", () => { }, 0, false, true, true)
    const formData = new FormData()
    formData.set("verify", email)
    const [ok, responce] = await SendPOSTDataToServerAsync("../verify.php", formData)
    progress.CloseDialog()
    if (!ok) {
        SendToast("Nelze přenést účet na jiný Email!", "Změny nemohly být uloženy.", "error")
        await dialogManager.OpenAlert("Přenos účtu na jiný Email", "Změny nemohly být uloženy, opakujte akci později.", true, true)
        return
    }
    window.open("../verify.php", "_blank");
    await dialogManager.OpenAlert("Přenos účtu na jiný Email", "Dokončete proces v novém okně. Dokud nevložíte správný kód, zachová se původní Email.", true, true);
    window.location.reload()
})

//Make attendant delete button work
for (const btn of document.getElementsByClassName("btnDeleteAttendant")) {
    btn.addEventListener("click", async () => {
        //Ask for confirm
        const confirm = await dialogManager.OpenConfirm("Odebrat zájemce?", "Opravdu chcete odebrat zájemce? Tento krok nelze vzít zpět.", true, true)
        if (!confirm) {
            SendToast("Odebrat zájemce", "Akce zrušena.", "info")
            return
        }

        //Send delete request
        const progress = dialogManager.ShowProgress("Odebrat zájemce", "Probíhá odebírání zájemce, čekejte prosím...", () => { }, 0, false, true, true)
        const formData = new FormData()
        formData.set("action", "delete")
        formData.set("id", btn.getAttribute("attendant") as string)
        const [ok, responce] = await SendPOSTDataToServerAsync("./attendant.php", formData)
        if (ok) {
            SendToast("Odebrat zájemce", "Zájemce odebrán úspěšně", "ok")
            setTimeout(() => {
                window.location.reload()
            }, 1000)
            return;
        }

        //Handle errors
        progress.CloseDialog()
        if (responce == "has subevents") {
            SendToast("Nelze smazat zájemce", "Zájemce je přihlášen na nějaké akci.", "error")
            return
        }
        SendToast("Odebrat zájemce", "Zájemce nemohl být odebrán.", "error")
        await dialogManager.OpenAlert("Odebrat zájemce", "Informace o odebrání nemohly být uloženy, opakujte akci později.", true, true)
    })
}

document.getElementById("icon")?.addEventListener("click", async (e) => {
    let file = await dialogManager.OpenPrompt<null | any>("Logo", "Vyberte soubor. Musí být v poměru 1:1.", null, "file")
    if (file && file[0]) {
        SendToast("Nahrávání souboru", "Soubor úspěšně nahrán", "ok")
        let data = new FormData()
        data.append('files[]', file[0]);
        data.append("id", (e.target as HTMLButtonElement).getAttribute("company") as string)
        let [ok, res] = await SendPOSTDataToServerAsync("./company.php", data);
        if (ok) {
            SendToast("Odpověď serveru", res, "ok");
            window.location.reload()
        } else SendToast("Odpověď serveru", res, "error")
    } else {
        SendToast("Nahrávání souboru", "Soubor se nepodařilo nahrát", "error")
        return;
    }

})

//Make attendant change school field work
const getSchoolsStart = async () => {
    const progress = dialogManager.ShowProgress("Načítání dat", "Probíhá načítání dat, čekejte prosím...", () => { }, 0, false, true, true)
    for (const element of document.getElementsByClassName("schoolValue")) {
        const attendantSchool = element as HTMLFormInputElement
        attendantSchool.validationFunction = async (value: string) => {
            const timestamp = new Date()
            const data = new FormData(undefined, null)
            console.log(attendantSchool.getValue()); data.set("query", attendantSchool.getValueRaw())
            const [ok, msg] = await SendPOSTDataToServerAsync("../assets/schoolSearch.php", data)
            const options = new Map()
            for (const school of JSON.parse(msg)) {
                console.log(school);
                options.set(school.name + " → " + school.address, school.id)
            }
            console.log(options);
            attendantSchool.setOptions(options, timestamp)
            return Promise.resolve(true);
        }
        await attendantSchool.validate()
    }
    SendToast("Načítání dat proběhlo úspěšně!", "Data načtena úspěšně.", "ok")
    progress.CloseDialog()
}
getSchoolsStart()
