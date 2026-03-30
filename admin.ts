import { FormDialogManager } from "./formWebScripts/js/formDialogScript.js";
import { KeyValuePair } from "./formWebScripts/js/sharedScripts.js";

const dialogManager = new FormDialogManager()

async function deleteUser(userId: string, name: string) {
    await dialogManager.OpenConfirm("Smazání uživatele", "Opravdu chcete odebrat uživatele: " + name + "?", true, true)
}

//Make User delete buttons work
for (const button of document.getElementsByClassName("deleteUserButton")) {
    (button as HTMLButtonElement).addEventListener("click", async () => {
        await deleteUser(button.getAttribute("userId"), button.getAttribute("userName"))
    })
}

//Make Parent of user clickable
for (const button of document.getElementsByClassName("parentOfUserCell")) {
    (button as HTMLButtonElement).addEventListener("click", async () => {
        console.log(await dialogManager.OpenSelect<Number>("Vyberte akci", "Co chcete provést?", 0, [new KeyValuePair("Napsat", 1), new KeyValuePair("Zobrazit komunikaci", 2)]))
    })
}