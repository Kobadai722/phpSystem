document.addEventListener("DOMContentLoaded", () => {
  // HTMLの要素を取得
  const toggleButton = document.getElementById("sidebar-toggle-button");
  const body = document.body;

  // ボタンが存在する場合のみ処理を実行
  if (toggleButton) {
    // ボタンがクリックされたときの処理
    toggleButton.addEventListener("click", () => {
      // bodyに 'sidebar-collapsed' クラスを付けたり外したりする
      body.classList.toggle("sidebar-collapsed");
    });
  }
});
