const comments = [
    "人民の幸福こそが、我が人生の最高の目標である。",
    "自力更生こそ、我が共和国の揺るぎない礎である。",
    "核兵器は、我が国の主権と安全を守るための正当な手段である。",
    "我々は、常に人民と共にあり、未来へと進む！",
    "敵対勢力のいかなる挑発も、断固として粉砕する。",
    "食料問題は、我が党が最も重視する課題の一つである。",
    "科学技術の発展こそ、国の繁栄を約束する道である。",
    "我々の社会主義は、勝利ある未来を約束する。",
    "偉大なる朝鮮労働党の指導の下、我々は常に勝利する。",
    "人民の笑顔が、我々にとって最大の喜びである。",
    "アメリカ帝国主義者たちは、必ずや歴史の審判を受けるだろう。",
    "我々の国防力は、日々強化されている。",
    "世界は、我が共和国の意志を理解するだろう。",
    "青年は、祖国の未来を担う希望である。",
    "常に警戒を怠らず、祖国を守り抜け！",
    "一寸の譲歩もなく、我が道を突き進む。",
    "我が国は、決して帝国主義者の足元にひざまずくことはない。",
    "強盛国家建設に向け、全人民が力を合わせよう！",
    "人民の生命と財産は、我が党が守り抜く。",
    "全ての困難を乗り越え、より良い明日を築き上げる。"
];

const images = [
    "/images/kimu.png",
    "/images/kimu1.png",
    "/images/kimu2.png",
    "/images/kimu3.png"
];

const commentDisplay = document.getElementById('president-comment');
const imageDisplay = document.getElementById('kimu-image');
const newCommentBtn = document.getElementById('new-comment-btn');

function displayRandomCommentAndImage() {
    // フェードアウト
    commentDisplay.classList.add('fade-out');
    imageDisplay.classList.add('fade-out');

    setTimeout(() => {
        const randomComment = comments[Math.floor(Math.random() * comments.length)];
        const randomImage = images[Math.floor(Math.random() * images.length)];
        commentDisplay.textContent = randomComment;
        imageDisplay.src = randomImage;

        // フェードイン
        commentDisplay.classList.remove('fade-out');
        imageDisplay.classList.remove('fade-out');
    }, 3000); // 0.5秒後に切り替え
}

// 初期表示
displayRandomCommentAndImage();

// ボタンイベント
newCommentBtn.addEventListener('click', displayRandomCommentAndImage);
