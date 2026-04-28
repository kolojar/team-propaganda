import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { setupSaveCancelButtons } from "./sharedScripts.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
setupSaveCancelButtons(dialogManager, "classroomValidate", "./classrooms.php", "./classroom.php", urlSearchParams.get("classroom"));
//# sourceMappingURL=classroom.js.map