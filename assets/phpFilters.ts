import { FormDialogCheckboxSelectData, FormDialogManager, GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { DiffArrays } from "../formWebScripts/js/sharedScripts.js";

function filterFunction(filter: HTMLFieldSetElement, aditionalParams: string[], removedParams: string[]) {
    //Get all form-inputs
    const params = new URLSearchParams(window.location.search);
    for (const element of filter.querySelectorAll("form-input")) {
        if (!element.hasAttribute("getter")) { continue }
        params.set(element.getAttribute("getter") as string, element.value)
    }

    //Add aditionalParams
    for (const element of aditionalParams) {
        params.set(element, "");
    }

    //Remove removedParams
    for (const element of removedParams) {
        params.delete(element);
    }
    params.set("!page", "1")
    window.location.replace("?" + params.toString())
}

for (const fieldset of document.querySelectorAll("fieldset[filter]")) {
    //Get all form-inputs
    for (const element of fieldset.querySelectorAll("form-input")) {
        if (!element.hasAttribute("getter")) { continue }
        element.addEventListener("keydown", (ev) => {
            if (ev.key == "Enter") {
                filterFunction(fieldset as HTMLFieldSetElement, [], [])
            }
        })
    }

    //Manage filters button
    const manageFiltersButton = fieldset.getElementsByClassName("btnManageFilters").item(0)
    manageFiltersButton?.addEventListener("click", async () => {
        //Get filters form PHG
        const filtersDialog = new Map<string, FormDialogCheckboxSelectData<string>>()
        const filters = JSON.parse(manageFiltersButton.getAttribute("filters") as string)
        const activeFiltersBefore = []
        for (const [key, value] of Object.entries(filters)) {
            const valueTyped = value as any;
            const active = valueTyped[0] == true
            if (active) { activeFiltersBefore.push(key) }
            filtersDialog.set(valueTyped[1], { value: key, checked: active })
        }

        //Select new filters
        const activeFiltersAfter = await GlobalDialogManager.ShowCheckboxSelectAsync("Vybrat filtry", "Vyberte, které filtry chcete nastavit.", null, filtersDialog, { blockOpenOver: true, openOverOthers: true })
        if (activeFiltersAfter == null) {
            SendToast("Vybrat filty", "Výběr filtrů zrušen.", "info")
            return
        }

        //Process diff
        const [add, remove] = DiffArrays(activeFiltersBefore, activeFiltersAfter as string[])
        filterFunction(fieldset as HTMLFieldSetElement, add, remove)
    })

    //Displayed columns
    const displayButton = fieldset.getElementsByClassName("btnDisplay").item(0)
    displayButton?.addEventListener("click", async () => {
        //Get filters form PHG
        const displayersDialog = new Map<string, FormDialogCheckboxSelectData<string>>()
        const displayers = JSON.parse(displayButton.getAttribute("displayers") as string)
        for (const [key, value] of Object.entries(displayers)) {
            const valueTyped = value as any;
            const active = valueTyped[0] == true
            displayersDialog.set(valueTyped[1], { value: key, checked: active })
        }

        //Select new filters
        const activeDisplayers = await GlobalDialogManager.ShowCheckboxSelectAsync("Vybrat zobrazované sloupce", "Vyberte, které sloupce chcete zobrazit.", null, displayersDialog, { blockOpenOver: true, openOverOthers: true })
        if (activeDisplayers == null) {
            SendToast("Vybrat zobrazované sloupce", "Výběr zobrazovaných sloupců zrušen.", "info")
            return
        }

        //Add aditionalParams
        const params = new URLSearchParams(window.location.search);
        params.set("!display", (activeDisplayers as string[]).join(","))
        window.location.replace("?" + params.toString())
    })

    //Filter button
    fieldset.getElementsByClassName("btnFilter").item(0)?.addEventListener("click", () => {
        filterFunction(fieldset as HTMLFieldSetElement, [], []);
    })

    //First page button
    const firstPageButton = fieldset.getElementsByClassName("btnFirstPage").item(0)
    firstPageButton?.addEventListener("click", () => {
        const params = new URLSearchParams(window.location.search);
        params.set("!page", "1");
        window.location.replace("?" + params.toString())
    })

    //Previous page button
    const changePageButton = fieldset.getElementsByClassName("btnChangePage").item(0)
    const prevPageButton = fieldset.getElementsByClassName("btnPrevPage").item(0)
    prevPageButton?.addEventListener("click", () => {
        const params = new URLSearchParams(window.location.search);
        params.set("!page", (parseInt(changePageButton?.getAttribute("page") ?? "2") - 1).toString());
        window.location.replace("?" + params.toString())
    })

    //Change page button
    changePageButton?.addEventListener("click", async () => {
        const originalNumber = changePageButton?.getAttribute("page") ?? "1"
        const newPage = await GlobalDialogManager.ShowPromptAsync<null | number>("Zadejte číslo stránky", "Zadejte číslo stránky, kterou chcete otevřít.", null, "number", { min: "1", placeholder: originalNumber, presetValue: originalNumber })
        if (newPage == null) {
            SendToast("Zadejte číslo stránky", "Zadání čísla stránky bylo zrušeno.", "info")
            return
        }
        const params = new URLSearchParams(window.location.search);
        params.set("!page", newPage.toString());
        window.location.replace("?" + params.toString())
    })

    //Change step button
    const changePageStepButton = fieldset.getElementsByClassName("btnChangePageStep").item(0)
    changePageStepButton?.addEventListener("click", async () => {
        const originalNumber = changePageStepButton?.getAttribute("step") ?? "10"
        const newPage = await GlobalDialogManager.ShowPromptAsync<null | number>("Zadejte počet položek na stránku", "Zadejte počet položek, které chcete zobratit.", null, "number", { min: "1", placeholder: originalNumber, presetValue: originalNumber })
        if (newPage == null) {
            SendToast("Zadejte číslo stránky", "Zadání čísla stránky bylo zrušeno.", "info")
            return
        }
        const params = new URLSearchParams(window.location.search);
        params.set("!pageStep", newPage.toString());
        window.location.replace("?" + params.toString())
    })

    //Next page button
    const nextPageButton = fieldset.getElementsByClassName("btnNextPage").item(0)
    nextPageButton?.addEventListener("click", () => {
        const params = new URLSearchParams(window.location.search);
        params.set("!page", (parseInt(changePageButton?.getAttribute("page") ?? "0") + 1).toString());
        window.location.replace("?" + params.toString())
    })

    //Get all headers
    const orderMap = new Map<string, string>();
    for (const element of document.querySelectorAll("th[filter='" + fieldset.getAttribute("filter") + "']")) {
        //Setup click event
        (element as HTMLElement).style.cursor = "pointer";
        if(element.getAttribute("getter")?.startsWith("!")) {(element as HTMLElement).style.cursor = "not-allowed"; continue;} 
        element.addEventListener("click", () => {
            const split = element.getAttribute("order")?.split(".")
            const params = new URLSearchParams(window.location.search);
            let order = params.get("!order") != null ? (params.get("!order") as string).split(",") : [];
            if (split == undefined) {
                //Add
                order.push(element.getAttribute("getter") as string)
            } else if (split[1] == "A") {
                //Switch
                for (let i = 0; i < order.length; i++) {
                    if(order[i] == element.getAttribute("getter") as string) {
                        order[i] = "!" + order[i];
                        break
                    }
                }
            } else if (split[1] == "D") {
                //Remove
                const getter =element.getAttribute("getter") as string
                order = order.filter(x => x != getter)
                order = order.filter(x => x != "!" + getter)
                console.log(order);
                
            }
            params.set("!order",order.join(","))
            window.location.replace("?" + params.toString())
        })

        //Setup arrows
        if (!element.hasAttribute("order")) { continue }
        const split = element.getAttribute("order")?.split(".")
        if (split != undefined) {
            orderMap.set(split[0], split[1])
            if (split[1] == "A") {
                element.innerHTML += "↑";
            } else if (split[1] == "D") {
                element.innerHTML += "↓";
            }
        }
    }
}