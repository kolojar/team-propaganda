import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";

const urlSearchParams = new URLSearchParams(window.location.search);
SetupSaveCancelButtons(null,"./companies.php","./company.php",urlSearchParams.get("company") as string)