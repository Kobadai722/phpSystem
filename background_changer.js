document.addEventListener('DOMContentLoaded', () => {
    const backgroundInput = document.getElementById('backgroundInput');
    const mainBody = document.getElementById('mainBody'); // bodyタグに設定したID

    // 保存された背景画像を適用、またはデフォルトを設定
    const savedBackgroundImage = localStorage.getItem('customBackgroundImage');
    if (savedBackgroundImage) {
        mainBody.style.backgroundImage = `url('${savedBackgroundImage}')`;
    } else {
        // デフォルトの背景画像をここに設定
        mainBody.style.backgroundImage = `url('/images/hassaan-here-GhUkIOvihpg-unsplash.jpg')`;
    }

    if (backgroundInput) {
        backgroundInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                // ファイルタイプをチェック
                if (file.type === 'image/png' || file.type === 'image/jpeg') {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const imageDataUrl = e.target.result;
                        // bodyの背景画像を変更
                        mainBody.style.backgroundImage = `url('${imageDataUrl}')`;
                        // localStorageに保存して、次回以降も適用されるようにする
                        localStorage.setItem('customBackgroundImage', imageDataUrl);
                    };
                    reader.readAsDataURL(file); // ファイルをData URLとして読み込む
                } else {
                    alert('PNGまたはJPEG形式の画像ファイルを選択してください。');
                    backgroundInput.value = ''; // 不適切なファイルをクリア
                }
            }
        });
    }
});