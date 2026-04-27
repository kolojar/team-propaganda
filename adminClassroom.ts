import { FormDialogManager } from "./formWebScripts/js/formDialogScript.js";
import { SendToast } from "./formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "./formWebScripts/js/serverComunication.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)