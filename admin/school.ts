import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
import { KeyValuePair } from "../formWebScripts/js/sharedScripts.js";
import { setupButtons } from "./sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
setupButtons(dialogManager,"schoolValidate","./schools.php","./school.php",urlSearchParams.get("school") as string)