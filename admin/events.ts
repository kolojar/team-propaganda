import { SendToast } from "../formWebScripts/js/formScript.js";
import { setupTableDeleteButtons } from "../assets/sharedScripts.js";

const urlSearchParams = new URLSearchParams(window.location.search)
setupTableDeleteButtons("./event.php","event")
setupTableDeleteButtons("./subevent.php","subevent")
setupTableDeleteButtons("./companyDay.php","companyDay")

if(urlSearchParams.has("noEventId")) {
    SendToast("Nastavení události","Není vybrána žádná událost pro zájemce!","error")
}
if(urlSearchParams.has("noSubeventId")) {
    SendToast("Nastavení události","Není vybrána žádná podudálost!","error")
}
if(urlSearchParams.has("noCompanyDayId")) {
    SendToast("Nastavení události","Není vybrán žádný den firem!","error")
}
if(urlSearchParams.has("invalidCombination")) {
    SendToast("Nastavení události","Neplatná kombinace události a dnu firem!","error")
}

//Setup btnTableSelectEvent buttons
//for (const button of document.getElementsByClassName("btnTableSelectEvent")) {
//    button.addEventListener("click",() => {
//        
//    })
//}