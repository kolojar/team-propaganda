import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
import { SendPOSTDataToServer, SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
SetupSaveCancelButtons(dialogManager, null, "./events.php", "./subevent.php", urlSearchParams.get("subevent") as string, "subeventValidate")

//Setup minimums and maximums
const startTime = (document.getElementById("start_time") as HTMLFormInputElement)
const endTime = (document.getElementById("end_time") as HTMLFormInputElement)
const date = (document.getElementById("date") as HTMLFormInputElement)

startTime.addEventListener("validation-done", () => {
    endTime.setMinimum(startTime.getValue())
})
date.addEventListener("validation-done", () => {
    console.log(date.getValue() == date.getMinimum());
    if (date.getValue() == date.getMinimum()) {
        startTime.setMinimum(date.getAttribute("minTime") as string)
    } else {
        startTime.setMinimum("")
    }
    if (date.getValue() == date.getMaximum()) {
        endTime.setMaximum(date.getAttribute("maxTime") as string)
    } else {
        endTime.setMaximum("")
    }
})
date.validate()

//Setup add classroom
document.getElementById("addClassroom")?.addEventListener("click", async () => {
    //Fetch all classrooms
    const progress = dialogManager.ShowProgress("Získávání seznamu učeben", "Probíhá získávání seznamu učeben, čekejte prosím...", () => { }, 0, false, true, true)
    const formData1 = new FormData()
    formData1.set("action", "getFunctionalClassrooms");
    const [ok1, resp1] = await SendPOSTDataToServerAsync("./classrooms.php", formData1)
    if (!ok1) {
        SendToast("Nelze získat seznam učeben!", "Nepodařilo se získat seznam učeben.", "error")
        progress.CloseDialog()
        await dialogManager.OpenAlert("Získávání seznamu učeben", "Nelze získat seznam učeben, opakujte akci později.<br>Důvod: " + resp1)
        return
    }

    //Process classrooms
    const classrooms = new Map<string, number>()
    for (const classroom of JSON.parse(resp1)) {
        classrooms.set(classroom.name + " → " + classroom.placesToSit + " míst", classroom.id)
    }
    progress.CloseDialog()
    const selectValue = await dialogManager.OpenSelect<null | number>("Přidat učebnu", "Vyberte učebnu ze seznamu. <i>Poznámka: Zobrazují se pouze aktivní učebny.</i>", null, classrooms, true, true)
    if (selectValue == null) {
        SendToast("Přidat učebnu", "Přidání učebny bylo zrušeno.", "info")
        return
    }

    //Send request to add classroom
    const progress2 = dialogManager.ShowProgress("Přidat učebnu", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true)
    const formData2 = new FormData()
    formData2.set("action", "addClassroom")
    formData2.set("id", urlSearchParams.get("subevent") as string)
    formData2.set("classroom", selectValue.toString())
    const [ok2, resp2] = await SendPOSTDataToServerAsync("./subevent.php", formData2)
    if (!ok2) {
        SendToast("Nelze přidat učebnu!", "Změny nemohly být uloženy.", "error")
        progress2.CloseDialog()
        await dialogManager.OpenAlert("Přidat učebnu", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + resp2, true, true)
        return
    }
    SendToast("Přidání učebny proběhlo úspěšně!", "Změny uloženy.", "ok")
    //progress.SetMessage(0,"Změny uloženy")
    setTimeout(() => {
        window.location.reload()
    }, 1000)
})

//Setup remove classroom
for (const btn of document.getElementsByClassName("deleteClassroom")) {
    btn.addEventListener("click", async () => {
        //Confirm deletion
        if (!await dialogManager.OpenConfirm("Odebrat učebnu", "Opravdu chcete odebrat učebnu?", true, true)) {
            SendToast("Odebrat učebnu", "Odebrání učebny bylo zrušeno.", "info")
            return
        }

        //Send POST to server
        const progress = dialogManager.ShowProgress("Odebrat učebnu", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true)
        const formData = new FormData()
        formData.set("action", "removeClassroom")
        formData.set("id", urlSearchParams.get("subevent") as string)
        formData.set("classroom", btn.getAttribute("classroom") as string)
        const [ok1, resp1] = await SendPOSTDataToServerAsync("./subevent.php", formData)
        if (!ok1) {
            progress.CloseDialog()
            SendToast("Nelze odebrat učebnu!", "Změny nemohly být uloženy.", "error")
            await dialogManager.OpenAlert("Odebrat učebnu", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + resp1, true, true)
            return
        }

        //All OK
        SendToast("Odebrání učebny proběhlo úspěšně!", "Změny uloženy.", "ok")
        //progress.SetMessage(0,"Změny uloženy")
        setTimeout(() => {
            window.location.reload()
        }, 1000)
    })
}

//Add Toast for not enought places
if(document.getElementById("freeSpacesCount")?.getAttribute("ok") != "1") {
    SendToast("Nedostatečný počet míst v učebnách","Na tuto podudálost chybí místa v učebnách, přidejte prosím další.<br>Po vyřešení problému bude možné žáky automaticky rozřadit do učeben.","warn")
}