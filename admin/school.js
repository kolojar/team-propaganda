import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { setupButtons } from "./sharedScripts.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
setupButtons(dialogManager, "schoolValidate", "./schools.php", "./school.php", urlSearchParams.get("school"));
//# sourceMappingURL=school.js.map