var _a;
import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
SetupSaveCancelButtons(dialogManager, null, "./events.php", "./subevent.php", urlSearchParams.get("subevent"), "subeventValidate");
//Setup minimums and maximums
const startTime = document.getElementById("start_time");
const endTime = document.getElementById("end_time");
const date = document.getElementById("date");
startTime.addEventListener("validation-done", () => {
    endTime.setMinimum(startTime.getValue());
});
date.addEventListener("validation-done", () => {
    console.log(date.getValue() == date.getMinimum());
    if (date.getValue() == date.getMinimum()) {
        startTime.setMinimum(date.getAttribute("minTime"));
    }
    else {
        startTime.setMinimum("");
    }
    if (date.getValue() == date.getMaximum()) {
        endTime.setMaximum(date.getAttribute("maxTime"));
    }
    else {
        endTime.setMaximum("");
    }
});
date.validate();
//Setup add classroom
(_a = document.getElementById("addClassroom")) === null || _a === void 0 ? void 0 : _a.addEventListener("click", async () => {
    //Fetch all classrooms
    const progress = dialogManager.ShowProgress("Získávání seznamu učeben", "Probíhá získávání seznamu učeben, čekejte prosím...", () => { }, 0, false, true, true);
    const formData1 = new FormData();
    formData1.set("action", "getFunctionalClassrooms");
    const [ok1, resp1] = await SendPOSTDataToServerAsync("./classrooms.php", formData1);
    if (!ok1) {
        SendToast("Nelze získat seznam učeben!", "Nepodařilo se získat seznam učeben.", "error");
        progress.CloseDialog();
        await dialogManager.OpenAlert("Získávání seznamu učeben", "Nelze získat seznam učeben, opakujte akci později.<br>Důvod: " + resp1);
        return;
    }
    //Process classrooms
    const classrooms = new Map();
    for (const classroom of JSON.parse(resp1)) {
        classrooms.set(classroom.name + " → " + classroom.placesToSit + " míst", classroom.id);
    }
    progress.CloseDialog();
    const selectValue = await dialogManager.OpenSelect("Přidat učebnu", "Vyberte učebnu ze seznamu. <i>Poznámka: Zobrazují se pouze aktivní učebny.</i>", null, classrooms, true, true);
    if (selectValue == null) {
        SendToast("Přidat učebnu", "Přidání učebny bylo zrušeno.", "info");
        return;
    }
    //Send request to add classroom
    const progress2 = dialogManager.ShowProgress("Přidat učebnu", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true);
    const formData2 = new FormData();
    formData2.set("action", "addClassroom");
    formData2.set("id", urlSearchParams.get("subevent"));
    formData2.set("classroom", selectValue.toString());
    const [ok2, resp2] = await SendPOSTDataToServerAsync("./subevent.php", formData2);
    if (!ok2) {
        SendToast("Nelze přidat učebnu!", "Změny nemohly být uloženy.", "error");
        progress2.CloseDialog();
        await dialogManager.OpenAlert("Přidat učebnu", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + resp2, true, true);
        return;
    }
    SendToast("Přidání učebny proběhlo úspěšně!", "Změny uloženy.", "ok");
    //progress.SetMessage(0,"Změny uloženy")
    setTimeout(() => {
        window.location.reload();
    }, 1000);
});
//# sourceMappingURL=subevent.js.map