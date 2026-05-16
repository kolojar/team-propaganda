import { SetupSaveCancelButtons } from "../assets/sharedScripts.js"
import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js"
import { HTMLFormInputElement } from "../formWebScripts/js/formScript.js"
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js"

localStorage.setItem("formLanguage", "cs")
const dialogManager = new FormDialogManager()
SetupSaveCancelButtons(dialogManager, "userInfo", ".", "./user.php", "-")
for (const element of document.getElementsByClassName("attendantInfo")) {
    SetupSaveCancelButtons(dialogManager, element as HTMLElement, ".", "./attendant.php", element.getAttribute("attendant") as string)
}

//Make attendant change school field work
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
    attendantSchool.validate()
}