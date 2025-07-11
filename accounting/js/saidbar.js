// HTMLの要素を取得
const toggleButton = document.querySelector(".sidebar-toggle button");
const body = document.body;

// ボタンがクリックされたときの処理
// ※toggleButtonが存在する場合のみ処理を実行する
if (toggleButton) {
  toggleButton.addEventListener("click", () => {
    // bodyに .sidebar-collapsed クラスを付けたり外したりする
    body.classList.toggle("sidebar-collapsed");
  });
}
