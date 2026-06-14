import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";

const urlSearchParams = new URLSearchParams(window.location.search)
SetupSaveCancelButtons(null,"./classrooms.php","./classroom.php",urlSearchParams.get("classroom") as string,"classroomValidate")