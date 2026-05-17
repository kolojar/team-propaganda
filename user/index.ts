import { SetupSaveCancelButtons } from "../assets/sharedScripts.js"
import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js"
import { HTMLFormInputElement, SendToast } from "../formWebScripts/js/formScript.js"
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js"

localStorage.setItem("formLanguage", "cs")
const dialogManager = new FormDialogManager()
SetupSaveCancelButtons(dialogManager, "userInfo", ".", "./user.php", "-")
for (const element of document.getElementsByClassName("attendantInfo")) {
    SetupSaveCancelButtons(dialogManager, element as HTMLElement, ".", "./attendant.php", element.getAttribute("attendant") as string)
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
    const progress = dialogManager.ShowProgress("Přenos účtu na jiný Email", "Probíhá vytváření požadavku, čekejte prosím...", () => { }, 0, false,true, true)
    const formData = new FormData()
    formData.set("verify", email)
    const [ok, responce] = await SendPOSTDataToServerAsync("../klal/verify.php", formData)
    progress.CloseDialog()
    if (!ok) {
        SendToast("Nelze přenést účet na jiný Email!", "Změny nemohly být uloženy.", "error")
        await dialogManager.OpenAlert("Přenos účtu na jiný Email", "Změny nemohly být uloženy, opakujte akci později.", true, true)
        return
    }
    window.open("../klal/verify.php","_blank");
    await dialogManager.OpenAlert("Přenos účtu na jiný Email", "Dokončete proces v novém okně. Dokud nevložíte správný kód, zachová se původní Email.", true, true);
    window.location.reload()
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