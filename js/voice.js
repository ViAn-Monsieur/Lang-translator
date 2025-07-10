let voices = [];
let translatedText = "";

// Load voice list
speechSynthesis.onvoiceschanged = () => {
  voices = speechSynthesis.getVoices();
};

const langMap = {
  en: "en-US",
  vi: "vi-VN",
  fr: "fr-FR",
  ja: "ja-JP",
  zh: "zh-CN",
  ko: "ko-KR",
  es: "es-ES",
  de: "de-DE",
  ru: "ru-RU",
  th: "th-TH",
};

// Bắt đầu nhận diện giọng nói
function startListening() {
  if (!("webkitSpeechRecognition" in window)) {
    alert("Trình duyệt của bạn không hỗ trợ Speech Recognition.");
    return;
  }

  const recognition = new webkitSpeechRecognition();
  recognition.lang =
    langMap[document.getElementById("sourceLang").value] || "vi-VN";
  recognition.interimResults = false;
  recognition.maxAlternatives = 1;

  recognition.start();
  recognition.onresult = (event) => {
    const speechText = event.results[0][0].transcript;
    document.getElementById("inputText").value = speechText;
    autoTranslate(speechText);
  };

  recognition.onerror = (e) => {
    alert("Lỗi ghi âm: " + e.error);
  };
}

// Gửi nội dung đi dịch
function autoTranslate(text) {
  showLoading(true);
  const source = document.getElementById("sourceLang").value;
  const target = document.getElementById("targetLang").value;

  fetch("api/translate.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      text: text,
      source_lang: source,
      target_lang: target,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      translatedText = data.translated ?? "";
      document.getElementById("result").innerText = translatedText;
      const autoSpeak = document.getElementById("autoSpeakToggle").checked;
      if (autoSpeak) speakTranslated();
    })
    .catch((error) => {
      console.error("Lỗi dịch:", error);
      alert("Không thể dịch văn bản. Vui lòng thử lại sau.");
    })
    .finally(() => {
      showLoading(false); // Ẩn loading sau khi hoàn thành
    });
}

// Đọc văn bản gốc
function speakOriginal() {
  const text = document.getElementById("inputText").value;
  const lang = document.getElementById("sourceLang").value;
  speak(text, lang);
}

// Đọc kết quả dịch
function speakTranslated() {
  const lang = document.getElementById("targetLang").value;
  speak(translatedText, lang);
}

// Hàm đọc bằng giọng nói
function speak(text, lang) {
  if (!text || !window.speechSynthesis) return;

  const utterance = new SpeechSynthesisUtterance(text);
  const locale = langMap[lang] || "en-US";
  utterance.lang = locale;

  const matchedVoice = voices.find((v) => v.lang === locale);
  if (matchedVoice) utterance.voice = matchedVoice;

  speechSynthesis.cancel();
  speechSynthesis.speak(utterance);
}
// Đảo ngôn ngữ nguồn và đích
document.getElementById("swapLang").addEventListener("click", function () {
  const sourceSelect = document.getElementById("sourceLang");
  const targetSelect = document.getElementById("targetLang");

  // Không đảo nếu đang ở chế độ "Tự động"
  if (sourceSelect.value === "auto") return;

  // Hoán đổi giá trị
  const temp = sourceSelect.value;
  sourceSelect.value = targetSelect.value;
  targetSelect.value = temp;

  // Dịch lại nếu có nội dung
  reTranslateIfNeeded();
});

// Hàm hiển thị/ẩn loading
function showLoading(show) {
  const loadingDiv = document.getElementById("loadingIndicator");
  if (loadingDiv) loadingDiv.style.display = show ? "block" : "none";
}
let timer = null;

// Dịch tự động khi gõ
document.getElementById("inputText").addEventListener("input", function () {
  clearTimeout(timer);
  timer = setTimeout(() => {
    const text = this.value.trim();
    if (text) autoTranslate(text);
    else document.getElementById("result").innerText = "";
  }, 500);
});

// Dịch lại khi đổi ngôn ngữ
function reTranslateIfNeeded() {
  const text = document.getElementById("inputText").value.trim();
  if (text) {
    showLoading(true);
    autoTranslate(text);
  }
}

document
  .getElementById("sourceLang")
  .addEventListener("change", reTranslateIfNeeded);
document
  .getElementById("targetLang")
  .addEventListener("change", reTranslateIfNeeded);

// Hiển thị/ẩn loading indicator
function showLoading(show = true) {
  document.getElementById("loadingIndicator").style.display = show
    ? "block"
    : "none";
}

function copyText(elementId) {
  const textarea = document.getElementById(elementId);
  textarea.select();
  textarea.setSelectionRange(0, 99999);
  navigator.clipboard
    .writeText(textarea.value)
    .then(() => alert("Đã sao chép!"))
    .catch((err) => alert("Không sao chép được: " + err));
}

function setLang(source, target) {
  document.getElementById("sourceLang").value = source;
  document.getElementById("targetLang").value = target;
}

function showLoading(show = true) {
  const loader = document.getElementById("loadingIndicator");
  const resultBox = document.getElementById("result");

  loader.style.display = show ? "flex" : "none";

  if (show) {
    // Thêm hiệu ứng: ẩn tạm bản dịch cũ
    resultBox.classList.add("hide-placeholder");
    resultBox.style.opacity = "0"; // làm mờ nội dung cũ
  } else {
    resultBox.classList.remove("hide-placeholder");
    resultBox.style.opacity = "1"; // hiển thị lại bình thường
  }
}

