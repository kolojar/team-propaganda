import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
const urlSearchParams = new URLSearchParams(window.location.search);
SetupSaveCancelButtons(null, "./users.php", "./user.php", urlSearchParams.get("user"));
//# sourceMappingURL=user.js.map