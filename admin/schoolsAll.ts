import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { setupTableDeleteButtons } from "../assets/sharedScripts.js";

const dialogManager = new FormDialogManager()
setupTableDeleteButtons(dialogManager,"./school.php","school")

//Setup next page button