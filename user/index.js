import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
localStorage.setItem("formLanguage", "cs");
const dialogManager = new FormDialogManager();
SetupSaveCancelButtons(dialogManager, "userInfo", ".", "./user.php", "-");
for (const element of document.getElementsByClassName("attendantInfo")) {
    SetupSaveCancelButtons(dialogManager, element, ".", "./attendant.php", element.getAttribute("attendant"));
}
//Make attendant change school field work
const getSchoolsStart = async () => {
    const progress = dialogManager.ShowProgress("Načítání dat", "Probíhá načítání dat, čekejte prosím...", () => { }, 0, false, true, true);
    for (const element of document.getElementsByClassName("schoolValue")) {
        const attendantSchool = element;
        attendantSchool.validationFunction = async (value) => {
            const timestamp = new Date();
            const data = new FormData(undefined, null);
            console.log(attendantSchool.getValue());
            data.set("query", attendantSchool.getValueRaw());
            const [ok, msg] = await SendPOSTDataToServerAsync("../assets/schoolSearch.php", data);
            const options = new Map();
            for (const school of JSON.parse(msg)) {
                console.log(school);
                options.set(school.name + " → " + school.address, school.id);
            }
            console.log(options);
            attendantSchool.setOptions(options, timestamp);
            return Promise.resolve(true);
        };
        await attendantSchool.validate();
    }
    SendToast("Načítání dat proběhlo úspěšně!", "Data načtena úspěšně.", "ok");
    progress.CloseDialog();
};
getSchoolsStart();
//# sourceMappingURL=index.js.map