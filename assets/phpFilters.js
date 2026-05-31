var _a, _b, _c;
import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { DiffArrays } from "../formWebScripts/js/sharedScripts.js";
function filterFunction(filter, aditionalParams, removedParams) {
    //Get all form-inputs
    const params = new URLSearchParams(window.location.search);
    for (const element of filter.querySelectorAll("form-input")) {
        if (!element.hasAttribute("getter")) {
            continue;
        }
        params.set(element.getAttribute("getter"), element.value);
    }
    //Add aditionalParams
    for (const element of aditionalParams) {
        params.set(element, "");
    }
    //Remove removedParams
    for (const element of removedParams) {
        params.delete(element);
    }
    params.set("!page", "1");
    window.location.replace("?" + params.toString());
}
for (const fieldset of document.querySelectorAll("fieldset[filter]")) {
    //Get all form-inputs
    for (const element of fieldset.querySelectorAll("form-input")) {
        if (!element.hasAttribute("getter")) {
            continue;
        }
        element.addEventListener("keydown", (ev) => {
            if (ev.key == "Enter") {
                filterFunction(fieldset, [], []);
            }
        });
    }
    //Manage filters button
    const manageFiltersButton = fieldset.getElementsByClassName("btnManageFilters").item(0);
    manageFiltersButton === null || manageFiltersButton === void 0 ? void 0 : manageFiltersButton.addEventListener("click", async () => {
        //Get filters form PHG
        const filtersDialog = new Map();
        const filters = JSON.parse(manageFiltersButton.getAttribute("filters"));
        const activeFiltersBefore = [];
        for (const [key, value] of Object.entries(filters)) {
            const valueTyped = value;
            const active = valueTyped[0] == true;
            if (active) {
                activeFiltersBefore.push(key);
            }
            filtersDialog.set(valueTyped[1], { value: key, checked: active });
        }
        //Select new filters
        const activeFiltersAfter = await GlobalDialogManager.ShowCheckboxSelectAsync("Vybrat filtry", "Vyberte, které filtry chcete nastavit.", null, filtersDialog, { blockOpenOver: true, openOverOthers: true });
        if (activeFiltersAfter == null) {
            SendToast("Vybrat filty", "Výběr filtrů zrušen.", "info");
            return;
        }
        //Process diff
        const [add, remove] = DiffArrays(activeFiltersBefore, activeFiltersAfter);
        filterFunction(fieldset, add, remove);
    });
    //Displayed columns
    const displayButton = fieldset.getElementsByClassName("btnDisplay").item(0);
    displayButton === null || displayButton === void 0 ? void 0 : displayButton.addEventListener("click", async () => {
        //Get filters form PHG
        const displayersDialog = new Map();
        const displayers = JSON.parse(displayButton.getAttribute("displayers"));
        for (const [key, value] of Object.entries(displayers)) {
            const valueTyped = value;
            const active = valueTyped[0] == true;
            displayersDialog.set(valueTyped[1], { value: key, checked: active });
        }
        //Select new filters
        const activeDisplayers = await GlobalDialogManager.ShowCheckboxSelectAsync("Vybrat zobrazované sloupce", "Vyberte, které sloupce chcete zobrazit.", null, displayersDialog, { blockOpenOver: true, openOverOthers: true });
        if (activeDisplayers == null) {
            SendToast("Vybrat zobrazované sloupce", "Výběr zobrazovaných sloupců zrušen.", "info");
            return;
        }
        //Add aditionalParams
        const params = new URLSearchParams(window.location.search);
        params.set("!display", activeDisplayers.join(","));
        window.location.replace("?" + params.toString());
    });
    //Filter button
    (_a = fieldset.getElementsByClassName("btnFilter").item(0)) === null || _a === void 0 ? void 0 : _a.addEventListener("click", () => {
        filterFunction(fieldset, [], []);
    });
    //First page button
    const firstPageButton = fieldset.getElementsByClassName("btnFirstPage").item(0);
    firstPageButton === null || firstPageButton === void 0 ? void 0 : firstPageButton.addEventListener("click", () => {
        const params = new URLSearchParams(window.location.search);
        params.set("!page", "1");
        window.location.replace("?" + params.toString());
    });
    //Previous page button
    const changePageButton = fieldset.getElementsByClassName("btnChangePage").item(0);
    const prevPageButton = fieldset.getElementsByClassName("btnPrevPage").item(0);
    prevPageButton === null || prevPageButton === void 0 ? void 0 : prevPageButton.addEventListener("click", () => {
        var _a;
        const params = new URLSearchParams(window.location.search);
        params.set("!page", (parseInt((_a = changePageButton === null || changePageButton === void 0 ? void 0 : changePageButton.getAttribute("page")) !== null && _a !== void 0 ? _a : "2") - 1).toString());
        window.location.replace("?" + params.toString());
    });
    //Change page button
    changePageButton === null || changePageButton === void 0 ? void 0 : changePageButton.addEventListener("click", async () => {
        var _a;
        const originalNumber = (_a = changePageButton === null || changePageButton === void 0 ? void 0 : changePageButton.getAttribute("page")) !== null && _a !== void 0 ? _a : "1";
        const newPage = await GlobalDialogManager.ShowPromptAsync("Zadejte číslo stránky", "Zadejte číslo stránky, kterou chcete otevřít.", null, "number", { min: "1", placeholder: originalNumber, presetValue: originalNumber });
        if (newPage == null) {
            SendToast("Zadejte číslo stránky", "Zadání čísla stránky bylo zrušeno.", "info");
            return;
        }
        const params = new URLSearchParams(window.location.search);
        params.set("!page", newPage.toString());
        window.location.replace("?" + params.toString());
    });
    //Change step button
    const changePageStepButton = fieldset.getElementsByClassName("btnChangePageStep").item(0);
    changePageStepButton === null || changePageStepButton === void 0 ? void 0 : changePageStepButton.addEventListener("click", async () => {
        var _a;
        const originalNumber = (_a = changePageStepButton === null || changePageStepButton === void 0 ? void 0 : changePageStepButton.getAttribute("step")) !== null && _a !== void 0 ? _a : "10";
        const newPage = await GlobalDialogManager.ShowPromptAsync("Zadejte počet položek na stránku", "Zadejte počet položek, které chcete zobratit.", null, "number", { min: "1", placeholder: originalNumber, presetValue: originalNumber });
        if (newPage == null) {
            SendToast("Zadejte číslo stránky", "Zadání čísla stránky bylo zrušeno.", "info");
            return;
        }
        const params = new URLSearchParams(window.location.search);
        params.set("!pageStep", newPage.toString());
        window.location.replace("?" + params.toString());
    });
    //Next page button
    const nextPageButton = fieldset.getElementsByClassName("btnNextPage").item(0);
    nextPageButton === null || nextPageButton === void 0 ? void 0 : nextPageButton.addEventListener("click", () => {
        var _a;
        const params = new URLSearchParams(window.location.search);
        params.set("!page", (parseInt((_a = changePageButton === null || changePageButton === void 0 ? void 0 : changePageButton.getAttribute("page")) !== null && _a !== void 0 ? _a : "0") + 1).toString());
        window.location.replace("?" + params.toString());
    });
    //Get all headers
    const orderMap = new Map();
    for (const element of document.querySelectorAll("th[filter='" + fieldset.getAttribute("filter") + "']")) {
        //Setup click event
        element.style.cursor = "pointer";
        if ((_b = element.getAttribute("getter")) === null || _b === void 0 ? void 0 : _b.startsWith("!")) {
            element.style.cursor = "not-allowed";
            continue;
        }
        element.addEventListener("click", () => {
            var _a;
            const split = (_a = element.getAttribute("order")) === null || _a === void 0 ? void 0 : _a.split(".");
            const params = new URLSearchParams(window.location.search);
            let order = params.get("!order") != null ? params.get("!order").split(",") : [];
            if (split == undefined) {
                //Add
                order.push(element.getAttribute("getter"));
            }
            else if (split[1] == "A") {
                //Switch
                for (let i = 0; i < order.length; i++) {
                    if (order[i] == element.getAttribute("getter")) {
                        order[i] = "!" + order[i];
                        break;
                    }
                }
            }
            else if (split[1] == "D") {
                //Remove
                const getter = element.getAttribute("getter");
                order = order.filter(x => x != getter);
                order = order.filter(x => x != "!" + getter);
                console.log(order);
            }
            params.set("!order", order.join(","));
            window.location.replace("?" + params.toString());
        });
        //Setup arrows
        if (!element.hasAttribute("order")) {
            continue;
        }
        const split = (_c = element.getAttribute("order")) === null || _c === void 0 ? void 0 : _c.split(".");
        if (split != undefined) {
            orderMap.set(split[0], split[1]);
            if (split[1] == "A") {
                element.innerHTML += "↑";
            }
            else if (split[1] == "D") {
                element.innerHTML += "↓";
            }
        }
    }
}
//# sourceMappingURL=phpFilters.js.map