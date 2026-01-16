const translations = {
    ja: {
        company_name_en: "maruwa transportation co.,LTD.",
        slogan: "旅をつなぐ、笑顔を運ぶ。",
        nav_usage: "利用方法",
        nav_area: "対応エリア",
        nav_car: "車種紹介",
        nav_price: "料金検索",
        btn_start: "予約開始",
        btn_confirm: "確認",
        btn_back: "← 戻る"
    },
    en: {
        company_name_en: "Maruwa Transportation Co., LTD.",
        slogan: "Connecting journeys, carrying smiles.",
        nav_usage: "How to Use",
        nav_area: "Service Area",
        nav_car: "Vehicle Types",
        nav_price: "Fare Search",
        btn_start: "Start Reservation",
        btn_confirm: "Confirm",
        btn_back: "← Back"
    },
    ko: {
        company_name_en: "Maruwa Transportation Co., LTD.",
        slogan: "여정을 잇고, 미소를 나릅니다.",
        nav_usage: "이용 방법",
        nav_area: "대응 구역",
        nav_car: "차종 소개",
        nav_price: "요금 검색",
        btn_start: "예약 시작",
        btn_confirm: "확인",
        btn_back: "← 뒤로"
    }
};

function applyTranslation(lang) {
    const dict = translations[lang];
    if (!dict) return;

    document.querySelectorAll("[data-key]").forEach(el => {
        const key = el.dataset.key;
        if (dict[key]) el.textContent = dict[key];
    });

    localStorage.setItem("appLang", lang);
}

document.addEventListener("DOMContentLoaded", () => {
    const savedLang = localStorage.getItem("appLang") || "ja";
    const switcher = document.getElementById("language-switcher");
    if (switcher) {
        switcher.value = savedLang;
        switcher.addEventListener("change", e => {
            applyTranslation(e.target.value);
        });
    }
    applyTranslation(savedLang);
});
