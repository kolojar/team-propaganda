import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
SetupSaveCancelButtons(dialogManager,null,"./users.php","./user.php",urlSearchParams.get("user") as string)