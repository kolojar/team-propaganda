var _a, _b, _c, _d, _e;
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
const time_cjl = document.getElementById("time_cjl");
const time_mat = document.getElementById("time_mat");
const date = document.getElementById("date");
startTime.addEventListener("validation-done", () => {
    endTime.min = (startTime.value);
    time_cjl.min = (startTime.value);
    time_mat.min = (startTime.value);
});
endTime.addEventListener("validation-done", () => {
    time_cjl.max = (endTime.value);
    time_mat.max = (endTime.value);
});
date.addEventListener("validation-done", () => {
    console.log(date.value == date.min);
    if (date.value == date.min) {
        startTime.min = date.getAttribute("minTime");
    }
    else {
        startTime.min = ("");
    }
    if (date.value == date.max) {
        endTime.max = date.getAttribute("maxTime");
    }
    else {
        endTime.max = ("");
    }
});
date.validate();
//Setup add classroom
(_a = document.getElementById("addClassroom")) === null || _a === void 0 ? void 0 : _a.addEventListener("click", async () => {
    //Fetch all classrooms
    const progress = dialogManager.ShowProgress("Získávání seznamu učeben", "Probíhá získávání seznamu učeben, čekejte prosím...", () => { }, 0, false);
    const formData1 = new FormData();
    formData1.set("action", "getFunctionalClassrooms");
    const [ok1, resp1] = await SendPOSTDataToServerAsync("./classrooms.php", formData1);
    if (!ok1) {
        SendToast("Nelze získat seznam učeben!", "Nepodařilo se získat seznam učeben.", "error");
        progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
        await dialogManager.ShowAlertAsync("Získávání seznamu učeben", "Nelze získat seznam učeben, opakujte akci později.<br>Důvod: " + resp1);
        return;
    }
    //Process classrooms
    const classrooms = new Map();
    for (const classroom of JSON.parse(resp1)) {
        classrooms.set(classroom.name + " → " + classroom.placesToSit + " míst", classroom.id);
    }
    progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
    const selectValue = await dialogManager.ShowSelectAsync("Přidat učebnu", "Vyberte učebnu ze seznamu. <i>Poznámka: Zobrazují se pouze aktivní učebny.</i>", null, classrooms);
    if (selectValue == null) {
        SendToast("Přidat učebnu", "Přidání učebny bylo zrušeno.", "info");
        return;
    }
    //Send request to add classroom
    const progress2 = dialogManager.ShowProgress("Přidat učebnu", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false);
    const formData2 = new FormData();
    formData2.set("action", "addClassroom");
    formData2.set("id", urlSearchParams.get("subevent"));
    formData2.set("classroom", selectValue.toString());
    const [ok2, resp2] = await SendPOSTDataToServerAsync("./subevent.php", formData2);
    if (!ok2) {
        SendToast("Nelze přidat učebnu!", "Změny nemohly být uloženy.", "error");
        progress2 === null || progress2 === void 0 ? void 0 : progress2.CloseDialog();
        await dialogManager.ShowAlertAsync("Přidat učebnu", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + resp2);
        return;
    }
    SendToast("Přidání učebny proběhlo úspěšně!", "Změny uloženy.", "ok");
    //progress.SetMessage(0,"Změny uloženy")
    setTimeout(() => {
        window.location.reload();
    }, 1000);
});
//Setup remove classroom
for (const btn of document.getElementsByClassName("deleteClassroom")) {
    btn.addEventListener("click", async () => {
        //Confirm deletion
        const nextLine = btn.getAttribute("count") == "0" ? "" : "<br>Pozor, v učebně jsou umístěni zájemci: " + btn.getAttribute("count") + "x";
        if (!await dialogManager.ShowConfirmAsync("Odebrat učebnu", "Opravdu chcete odebrat učebnu?" + nextLine)) {
            SendToast("Odebrat učebnu", "Odebrání učebny bylo zrušeno.", "info");
            return;
        }
        //Send POST to server
        const progress = dialogManager.ShowProgress("Odebrat učebnu", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false);
        const formData = new FormData();
        formData.set("action", "removeClassroom");
        formData.set("id", urlSearchParams.get("subevent"));
        formData.set("classroom", btn.getAttribute("classroom"));
        const [ok1, resp1] = await SendPOSTDataToServerAsync("./subevent.php", formData);
        if (!ok1) {
            progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
            SendToast("Nelze odebrat učebnu!", "Změny nemohly být uloženy.", "error");
            await dialogManager.ShowAlertAsync("Odebrat učebnu", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + resp1);
            return;
        }
        //All OK
        SendToast("Odebrání učebny proběhlo úspěšně!", "Změny uloženy.", "ok");
        //progress.SetMessage(0,"Změny uloženy")
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    });
}
//Add Toast for not enought places
if (((_b = document.getElementById("freeSpacesCount")) === null || _b === void 0 ? void 0 : _b.getAttribute("ok")) != "1" && !urlSearchParams.has("newSubevent")) {
    SendToast("Nedostatečný počet míst v učebnách", "Na tuto podudálost chybí místa v učebnách, přidejte prosím další.<br>Po vyřešení problému bude možné žáky automaticky rozřadit do učeben.", "warn");
}
//Setup move classroom
for (const btn of document.getElementsByClassName("moveClassroom")) {
    btn.addEventListener("click", async () => {
        //Fetch all classrooms
        const progress2 = dialogManager.ShowProgress("Získávání seznamu učeben", "Probíhá získávání seznamu učeben, čekejte prosím...", () => { }, 0, false);
        const formData1 = new FormData();
        formData1.set("action", "getFunctionalClassrooms");
        const [ok2, resp2] = await SendPOSTDataToServerAsync("./classrooms.php", formData1);
        if (!ok2) {
            SendToast("Nelze získat seznam učeben!", "Nepodařilo se získat seznam učeben.", "error");
            progress2 === null || progress2 === void 0 ? void 0 : progress2.CloseDialog();
            await dialogManager.ShowAlertAsync("Získávání seznamu učeben", "Nelze získat seznam učeben, opakujte akci později.<br>Důvod: " + resp2);
            return;
        }
        //Process classrooms
        const classrooms = new Map();
        for (const classroom of JSON.parse(resp2)) {
            classrooms.set(classroom.name + " → " + classroom.placesToSit + " míst", classroom.id);
        }
        progress2 === null || progress2 === void 0 ? void 0 : progress2.CloseDialog();
        //Confirm move
        const nextLine = btn.getAttribute("count") == "0" ? "" : "Pozor, v učebně jsou umístěni zájemci: " + btn.getAttribute("count") + "x<br>";
        const selectValue = await dialogManager.ShowSelectAsync("Přemístit žáky do jiné učebny", nextLine + "Vyberte prosím novou učebnu ze seznamu.", null, classrooms);
        if (selectValue == null) {
            SendToast("Přemístit žáky do jiné učebny", "Přemístění bylo zrušeno.", "info");
            return;
        }
        //Send POST to server
        const progress = dialogManager.ShowProgress("Přemístit žáky do jiné učebny", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false);
        const formData = new FormData();
        formData.set("action", "moveClassroom");
        formData.set("id", urlSearchParams.get("subevent"));
        formData.set("source_classroom", btn.getAttribute("classroom"));
        formData.set("target_classroom", selectValue.toString());
        const [ok1, resp1] = await SendPOSTDataToServerAsync("./subevent.php", formData);
        if (!ok1) {
            progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
            SendToast("Nelze přemístit žáky do jiné učebny!", "Změny nemohly být uloženy.", "error");
            await dialogManager.ShowAlertAsync("Přemístit žáky do jiné učebny", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + resp1);
            return;
        }
        //All OK
        SendToast("Odebrání učebny proběhlo úspěšně!", "Změny uloženy.", "ok");
        //progress.SetMessage(0,"Změny uloženy")
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    });
}
//Add Toast for not attendants outside of classroom
if (((_c = document.getElementById("withoutClassroom")) === null || _c === void 0 ? void 0 : _c.getAttribute("count")) != "0" && !urlSearchParams.has("newSubevent")) {
    SendToast("Žáci mimo učebny", "V této podudálosti jsou žáci mimo učebny.<br>Prosím, rozřaďte je.", "warn");
}
//Setup sort attendants
(_d = document.getElementById("sortAttendants")) === null || _d === void 0 ? void 0 : _d.addEventListener("click", async () => {
    var _a, _b;
    const force = await dialogManager.ShowConfirmAsync("Rozřadit zájemce do učeben", "Přejete si provést změnu pro VŠECHNY, tedy i již rozřazené, zájemce?");
    if (!await dialogManager.ShowConfirmAsync("Rozřadit zájemce do učeben", "Opravdu chcete pokračovat?<br>Rozřazení ovlivní " + (force ? "VŠECHNY zájemce." : "POUZE zájemce BEZ UČEBNY."))) {
        SendToast("Rozřadit zájemce do učeben", "Rozřazení bylo zrušeno.", "info");
        return;
    }
    //Send request to add classroom
    const progress2 = dialogManager.ShowProgress("Rozřadit zájemce do učeben", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false);
    const formData2 = new FormData();
    formData2.set("action", "sortAttendants");
    formData2.set("id", urlSearchParams.get("subevent"));
    formData2.set("force", force ? "1" : "0");
    formData2.set("not_in_table", (_a = document.getElementById("withoutClassroom")) === null || _a === void 0 ? void 0 : _a.getAttribute("not-in-table"));
    formData2.set("in_table", (_b = document.getElementById("withoutClassroom")) === null || _b === void 0 ? void 0 : _b.getAttribute("in-table"));
    const [ok2, resp2] = await SendPOSTDataToServerAsync("./subevent.php", formData2);
    if (!ok2) {
        SendToast("Nelze rozřadit zájemce do učeben!", "Změny nemohly být uloženy.", "error");
        progress2 === null || progress2 === void 0 ? void 0 : progress2.CloseDialog();
        await dialogManager.ShowAlertAsync("Rozřadit zájemce do učeben", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + resp2);
        return;
    }
    SendToast("Rozřazení zájemců do učeben proběhlo úspěšně!", "Změny uloženy.", "ok");
    //progress.SetMessage(0,"Změny uloženy")
    setTimeout(() => {
        window.location.reload();
    }, 1000);
});
//Setup sort attendants
(_e = document.getElementById("copySettings")) === null || _e === void 0 ? void 0 : _e.addEventListener("click", async () => {
    //Fetch all subevents
    const progress1 = dialogManager.ShowProgress("Získávání seznamu podudálostí", "Probíhá získávání seznamu podudálostí, čekejte prosím...", () => { }, 0, false);
    const formData1 = new FormData();
    formData1.set("action", "getRelatedSubevents");
    formData1.set("id", urlSearchParams.get("subevent"));
    const [ok1, resp1] = await SendPOSTDataToServerAsync("./events.php", formData1);
    if (!ok1) {
        SendToast("Nelze získat seznam podudálostí!", "Nepodařilo se získat seznam podudálostí.", "error");
        progress1 === null || progress1 === void 0 ? void 0 : progress1.CloseDialog();
        await dialogManager.ShowAlertAsync("Získávání seznamu podudálostí", "Nelze získat seznam podudálostí, opakujte akci později.<br>Důvod: " + resp1);
        return;
    }
    //Process subevents
    const subevents = new Map();
    let i = 0;
    for (const subevent of JSON.parse(resp1)) {
        i++;
        if (subevent.id != urlSearchParams.get("subevent")) {
            subevents.set(i + ". → " + new Date(subevent.date).toLocaleDateString(), subevent.id);
        }
    }
    progress1 === null || progress1 === void 0 ? void 0 : progress1.CloseDialog();
    if (subevents.size == 0) {
        SendToast("Nelze kopírovat nastavení rozřazení!", "Nejsou k dispozici žádné další podudálosti u této události.", "error");
        return;
    }
    //Get subevent
    const subevent = await dialogManager.ShowSelectAsync("Kopírovat nastavení rozřazení", "Vyberte, ze které podudálosti chcete zkopírovat nastavení?", null, subevents);
    if (subevent == null) {
        SendToast("Kopírovat nastavení rozřazení", "Kopírování bylo zrušeno.", "info");
        return;
    }
    //Send request to add classroom
    const progress2 = dialogManager.ShowProgress("Kopírovat nastavení rozřazení", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false);
    const formData2 = new FormData();
    formData2.set("action", "copySettings");
    formData2.set("id", urlSearchParams.get("subevent"));
    formData2.set("source_id", subevent.toString());
    const [ok2, resp2] = await SendPOSTDataToServerAsync("./subevent.php", formData2);
    if (!ok2) {
        SendToast("Nelze kopírovat nastavení rozřazení!", "Změny nemohly být uloženy.", "error");
        progress2 === null || progress2 === void 0 ? void 0 : progress2.CloseDialog();
        await dialogManager.ShowAlertAsync("Kopírovat nastavení rozřazení", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + resp2);
        return;
    }
    SendToast("Kopírování nastavení rozřazení proběhlo úspěšně!", "Změny uloženy.", "ok");
    //progress.SetMessage(0,"Změny uloženy")
    setTimeout(() => {
        window.location.reload();
    }, 1000);
});
//# sourceMappingURL=subevent.js.map