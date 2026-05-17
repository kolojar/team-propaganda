import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
import { KeyValuePair } from "../formWebScripts/js/sharedScripts.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
SetupSaveCancelButtons(dialogManager,null,"./schools.php","./school.php",urlSearchParams.get("school") as string,"schoolValidate")