import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { setupSaveCancelButtons } from "./sharedScripts.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
setupSaveCancelButtons(dialogManager, "schoolValidate", "./schools.php", "./school.php", urlSearchParams.get("school"));
//# sourceMappingURL=school.js.map