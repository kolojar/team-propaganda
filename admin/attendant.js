import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
SetupSaveCancelButtons(dialogManager, "attendantValidate", "./attendants.php", "./attendant.php", urlSearchParams.get("user"));
//Make attendant change school field work
const attendantSchool = document.getElementById("school");
attendantSchool.validationFunction = async (value) => {
    const timestamp = new Date();
    const data = new FormData(undefined, null);
    console.log(attendantSchool.getValue());
    data.set("query", attendantSchool.getValueRaw());
    const [ok, msg] = await SendPOSTDataToServerAsync("./schoolSearch.php", data);
    const options = new Map();
    for (const school of JSON.parse(msg)) {
        console.log(school);
        options.set(school.name + " → " + school.address, school.id);
    }
    console.log(options);
    attendantSchool.setOptions(options, timestamp);
    return Promise.resolve(true);
};
attendantSchool.validate();
//# sourceMappingURL=attendant.js.map