import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement, HTMLFormToggleElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
import { setupButtons } from "./sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
setupButtons(dialogManager,"classroomValidate","./classrooms.php","./classroom.php",urlSearchParams.get("classroom") as string)