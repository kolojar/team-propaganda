import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { setupButtons } from "./sharedScripts.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
setupButtons(dialogManager, "classroomValidate", "./classrooms.php", "./classroom.php", urlSearchParams.get("classroom"));
//# sourceMappingURL=classroom.js.map