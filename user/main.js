var _a, _b, _c, _d;
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
const urlSearchParams = new URLSearchParams(window.location.search);
localStorage.setItem("formLanguage", "cs");
SetupSaveCancelButtons("userInfo", ".", "./user.php", "-");
for (const element of document.getElementsByClassName("attendantInfo")) {
    SetupSaveCancelButtons(element, ".", "./attendant.php", element.getAttribute("attendant"));
}
for (const element of document.getElementsByClassName("companyInfo")) {
    SetupSaveCancelButtons(element, ".", "./company.php", element.getAttribute("company"));
}
//Make move email button work
(_a = document.getElementById("btnChangeEmail")) === null || _a === void 0 ? void 0 : _a.addEventListener("click", async () => {
    //Ask for email
    const email = await GlobalDialogManager.ShowPromptAsync("Přenos na jiný účet", "Zadejte nový Email, kterým se budete přihlašovat do aplikace. Starý přístup zanikne.", null, "email", { placeholder: "Email" });
    if (email == null) {
        SendToast("Přenos účtu na jiný Email zrušen!", "Akce byla zrušena úspěšně.", "ok");
        return;
    }
    //Send POST
    const progress = GlobalDialogManager.ShowProgress("Přenos účtu na jiný Email", "Probíhá vytváření požadavku, čekejte prosím...", () => { }, 0, false);
    const formData = new FormData();
    formData.set("verify", email);
    const [ok, responce] = await SendPOSTDataToServerAsync("../verify.php", formData);
    progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
    if (!ok) {
        SendToast("Nelze přenést účet na jiný Email!", "Změny nemohly být uloženy.", "error");
        await GlobalDialogManager.ShowAlertAsync("Přenos účtu na jiný Email", "Změny nemohly být uloženy, opakujte akci později.");
        return;
    }
    window.open("../verify.php", "_blank");
    await GlobalDialogManager.ShowAlertAsync("Přenos účtu na jiný Email", "Dokončete proces v novém okně. Dokud nevložíte správný kód, zachová se původní Email.");
    window.location.reload();
});
//Make attendant delete button work
for (const btn of document.getElementsByClassName("btnDeleteAttendant")) {
    btn.addEventListener("click", async () => {
        //Ask for confirm
        const confirm = await GlobalDialogManager.ShowConfirmAsync("Odebrat zájemce?", "Opravdu chcete odebrat zájemce? Tento krok nelze vzít zpět.");
        if (!confirm) {
            SendToast("Odebrat zájemce", "Akce zrušena.", "info");
            return;
        }
        //Send delete request
        const progress = GlobalDialogManager.ShowProgress("Odebrat zájemce", "Probíhá odebírání zájemce, čekejte prosím...", () => { }, 0, false);
        const formData = new FormData();
        formData.set("action", "delete");
        formData.set("id", btn.getAttribute("attendant"));
        const [ok, responce] = await SendPOSTDataToServerAsync("./attendant.php", formData);
        if (ok) {
            SendToast("Odebrat zájemce", "Zájemce odebrán úspěšně", "ok");
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            return;
        }
        //Handle errors
        progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
        if (responce == "has subevents") {
            SendToast("Nelze smazat zájemce", "Zájemce je přihlášen na nějaké akci.", "error");
            return;
        }
        SendToast("Odebrat zájemce", "Zájemce nemohl být odebrán.", "error");
        await GlobalDialogManager.ShowAlertAsync("Odebrat zájemce", "Informace o odebrání nemohly být uloženy, opakujte akci později.");
    });
}
(_b = document.getElementById("icon")) === null || _b === void 0 ? void 0 : _b.addEventListener("click", async (e) => {
    let file = await GlobalDialogManager.ShowPromptAsync("Logo", "Vyberte soubor. Musí být v poměru 1:1.", null, "file");
    if (file && file[0]) {
        SendToast("Nahrávání souboru", "Soubor úspěšně nahrán", "ok");
        let data = new FormData();
        data.append('files[]', file[0]);
        data.append("id", e.target.getAttribute("company"));
        let [ok, res] = await SendPOSTDataToServerAsync("./company.php", data);
        if (ok) {
            SendToast("Odpověď serveru", res, "ok");
            window.location.reload();
        }
        else
            SendToast("Odpověď serveru", res, "error");
    }
    else if (file == null) {
        SendToast("Nahrávání souboru", "Akce zrušena", "info");
        return;
    }
    else {
        SendToast("Nahrávání souboru", "Soubor se nepodařilo nahrát", "error");
        return;
    }
});
(_c = document.getElementById("addNew")) === null || _c === void 0 ? void 0 : _c.addEventListener("click", async (e) => {
    let data = new FormData();
    data.append("action", "getCompanyDays");
    data.append("id", e.target.getAttribute("comp"));
    let [ok, res] = await SendPOSTDataToServerAsync("./user.php", data);
    if (!ok) {
        SendToast("Odpověď serveru", res, "error");
        return;
    }
    let resp = JSON.parse(res);
    let options = new Map();
    for (let row of resp) {
        let date = row[1].split("-");
        options.set(`${date[2]}. ${date[1]}. ${date[0]}`, row[0]);
    }
    let companyDayId = await GlobalDialogManager.ShowSelectAsync("Den firem", "<b style='color: red;'>Tento výběr je závazný.</b>", null, options);
    if (companyDayId == null)
        return;
    let data2 = new FormData();
    data2.append("action", "addCD");
    data2.append("idCD", companyDayId);
    data2.append("id", e.target.getAttribute("comp"));
    let [ok2, res2] = await SendPOSTDataToServerAsync("./user.php", data2);
    if (!ok2) {
        SendToast("Odpověď serveru", res2, "error");
    }
    else {
        SendToast("Odpověď serveru", res2, "ok");
        window.location.reload();
    }
});
(_d = document.getElementById("fieldSelect")) === null || _d === void 0 ? void 0 : _d.addEventListener("click", async (e) => {
    var _a;
    const companyId = e.target.getAttribute("company");
    const selectedFields = (_a = e.target.getAttribute("fields")) === null || _a === void 0 ? void 0 : _a.split(",");
    const data = new FormData();
    data.append("action", "getFields");
    const fields = JSON.parse((await SendPOSTDataToServerAsync("./company.php", data))[1]);
    const values = new Map();
    for (let field of fields) {
        if (!(selectedFields === null || selectedFields === void 0 ? void 0 : selectedFields.includes(field.id_fields)))
            values.set(field.name, { value: field.id_fields, checked: false });
        else
            values.set(field.name, { value: field.id_fields, checked: true });
    }
    let result = await GlobalDialogManager.ShowCheckboxSelectAsync("Obory", "Vyberte obory které by mohla vaše firma zajímat.", null, values);
    console.log(result);
    if (result == null) {
        SendToast("Výběr oborů", "Akce zrušena", "info");
        return;
    }
    let data2 = new FormData();
    data2.append("action", "addFields");
    data2.append("fields", JSON.stringify(result));
    data2.append("id", companyId);
    let [ok, res] = await SendPOSTDataToServerAsync("./company.php", data2);
    if (!ok) {
        SendToast("Odpověď serveru", res, "error");
    }
    else {
        SendToast("Odpověď serveru", res, "ok");
        window.location.reload();
    }
});
//Make attendant change school field work
const getSchoolsStart = async () => {
    const progress = GlobalDialogManager.ShowProgress("Načítání dat", "Probíhá načítání dat, čekejte prosím...", () => { }, 0, false);
    for (const element of document.getElementsByClassName("schoolValue")) {
        const attendantSchool = element;
        attendantSchool.validationFunction = async (value) => {
            const timestamp = new Date();
            const data = new FormData(undefined, null);
            //console.log(attendantSchool.value);
            if (attendantSchool.value == -10) {
                document.getElementsByClassName("visibility")[0].style.display = "block";
            }
            else {
                document.getElementsByClassName("visibility")[0].style.display = "none";
            }
            data.set("query", attendantSchool.valueRaw.toString());
            const [ok, msg] = await SendPOSTDataToServerAsync("../assets/schoolSearch.php", data);
            const options = new Map();
            for (const school of JSON.parse(msg)) {
                //console.log(school);
                options.set(school.name + " → " + school.address, school.id);
            }
            //console.log(options);
            options.set("Má škola není v seznamu.", -10);
            attendantSchool.setOptions(options, timestamp);
            attendantSchool.alwaysShownOptions = ["Má škola není v seznamu."];
            return Promise.resolve(true);
        };
        await attendantSchool.validate();
    }
    progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
};
getSchoolsStart();
//# sourceMappingURL=main.js.map