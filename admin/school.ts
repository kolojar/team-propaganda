import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";

const urlSearchParams = new URLSearchParams(window.location.search)
SetupSaveCancelButtons(null,"./schools.php","./school.php",urlSearchParams.get("school") as string)