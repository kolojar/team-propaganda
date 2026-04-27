import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
import { setupButtons } from "./sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
setupButtons(dialogManager,"attendantValidate","./attendants.php","./attendant.php",urlSearchParams.get("attendant") as string)

//Make attendant change school field work
const attendantSchool = document.getElementById("school") as HTMLFormInputElement
attendantSchool.validationFunction = async (value: string) => {
    const timestamp = new Date()
    const data = new FormData(undefined, null)
    data.set("action", "getSchools")
    data.set("table", "")
    console.log(attendantSchool.getValue()); data.set("query", attendantSchool.getValueRaw())
    const [ok, msg] = await SendPOSTDataToServerAsync("./adminFunctions.php", data)
    const options = new Map()
    for (const school of JSON.parse(msg)) {
        console.log(school);
        options.set(school.name + " → " + school.address,school.id)
    }
    console.log(options);
    attendantSchool.setOptions(options, timestamp)
    return Promise.resolve(true);
}
attendantSchool.validate()