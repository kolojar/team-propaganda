import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js"
import { SendToast } from "../formWebScripts/js/formScript.js"
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

for (const btn of document.getElementsByClassName("editSettingBtn")) {
    btn.addEventListener("click", async () => {
        //Enter value
        const newSetting = await GlobalDialogManager.ShowPromptAsync("Změnit nastavení", "Změnit hodnotu nastavení", null, "text", { presetValue: btn.getAttribute("setting-value") })
        if (newSetting == null) {
            SendToast("Změna nastavení zrušena", "Hodnota nebyla uložena", "info");
            return;
        }

        //Send POST
        const progress = GlobalDialogManager.ShowProgress("Změnit nastavení", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false)
        const formData = new FormData();
        formData.set("action", "set");
        formData.set("key", btn.getAttribute("setting-key") as string);
        formData.set("value", newSetting);
        const [ok, responce] = await SendPOSTDataToServerAsync("./settings.php", formData);
        if (!ok) {
            progress?.CloseDialog()
            SendToast("Nelze upravit nastavení!", "Změny nemohly být uloženy.", "error")
            await GlobalDialogManager.ShowAlertAsync("Změnit nastavení", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + responce)
            return
        }

        //All OK
        SendToast("Ukládání nastavení úspěšně!", "Změny uloženy.", "ok")
        //progress.SetMessage(0,"Změny uloženy")
        setTimeout(() => {
            window.location.reload()
        }, 1000)
    })
}