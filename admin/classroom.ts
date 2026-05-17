import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement, HTMLFormToggleElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
SetupSaveCancelButtons(dialogManager,null,"./classrooms.php","./classroom.php",urlSearchParams.get("classroom") as string)