import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
const dialogManager = new FormDialogManager();
for (const button of document.getElementsByClassName("btnUnregisterTable")) {
    button.addEventListener("click", async () => {
        if (!await dialogManager.ShowConfirmAsync("Odhlásit zájemce", "Opravdu chcete odhlásit zájemce?")) {
            SendToast("Odhlásit zájemce", "Odhlášení zájmece zrušeno.", "info");
            return;
        }
        const reason = await dialogManager.ShowPromptAsync("Odhlásit zájemce", "Zadejte důvod odhlášení.", null, "text", { placeholder: "Důvod odhlášení" });
        if (reason == null) {
            SendToast("Odhlásit zájemce", "Odhlášení zájmece zrušeno.", "info");
            return;
        }
        //Send XHR
        const progress = dialogManager.ShowProgress("Odhlásit zájemce", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false);
        const formData = new FormData();
        formData.set("action", "unregister");
        formData.set("id", button.getAttribute("variableSymbol"));
        formData.set("reason", reason);
        const [ok, responce] = await SendPOSTDataToServerAsync("./payments.php", formData);
        if (!ok) {
            progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
            SendToast("Odhlásit zájemce", "Nepodařilo se odhlásit zájemce!", "error");
            await dialogManager.ShowAlertAsync("Odhlásit zájemce", "Nepodařilo se odhlásit zájemce, zkuste to prosím znovu a později.<br>Důvod: " + responce);
            return;
        }
        SendToast("Odhlásit zájemce", "Zájemce odhlášen!", "ok");
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    });
}
//Make attendant change school field work
const schools = async () => {
    for (const element of document.querySelectorAll("[filter-field-id='school']")) {
        const filterSchool = element;
        filterSchool.validationFunction = async (value) => {
            const timestamp = new Date();
            const data = new FormData(undefined, null);
            console.log(filterSchool.value);
            data.set("query", filterSchool.valueRaw.toString());
            const [ok, msg] = await SendPOSTDataToServerAsync("../assets/schoolSearch.php", data);
            const options = new Map();
            for (const school of JSON.parse(msg)) {
                console.log(school);
                options.set(school.name + " → " + school.address, school.name + " → " + school.address);
            }
            console.log(options);
            filterSchool.setOptions(options, timestamp);
            return Promise.resolve(true);
        };
        filterSchool.isStrictList = false;
        await filterSchool.validate();
    }
};
schools();
//# sourceMappingURL=attendants.js.map