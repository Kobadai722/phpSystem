const SAPPORO_AREA_CODE = '016010';
const WEATHER_API_BASE_URL = 'https://weather.tsukumijima.net/api/forecast';

async function fetchSapporoWeather() {
    try {
        const response = await fetch(`${WEATHER_API_BASE_URL}?city=${SAPPORO_AREA_CODE}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        const weatherInfoDiv = document.getElementById('weather-info');
        weatherInfoDiv.innerHTML = '';

        const todayForecast = data.forecasts[0];

        if (todayForecast) {
            const telop = todayForecast.telop;
            const iconUrl = todayForecast.image.url;
            
            // 最高気温の表示を調整
            const maxTemp = todayForecast.temperature.max;
            let maxTempDisplay = '情報なし';
            if (maxTemp && maxTemp.celsius) {
                maxTempDisplay = `${maxTemp.celsius}`;
            }

            const weatherHtml = `
                <div class="weather-main">
                    <p class="weather-telop">${telop}</p>
                    <img class="weather-img" src="${iconUrl}" alt="${telop}">
                    <div class="weather-temperature-info"> <div class="weather-max">最高気温</div>
                        <div class="weather-temp-value"> <div class="weather-temp">${maxTempDisplay}</div>
                            <div class="weather-c">°C</div>
                        </div>
                    </div>
                </div>
                <p class="weather-note">※ 気温情報が「情報なし」の場合、発表前または観測されていない可能性があります。</p>
            `;
            weatherInfoDiv.innerHTML = weatherHtml;
        } else {
            weatherInfoDiv.innerHTML = '<p>今日の天気予報が見つかりませんでした。</p>';
        }

    } catch (error) {
        console.error('天気情報の取得中にエラーが発生しました:', error);
        document.getElementById('weather-info').innerHTML = '<p>天気情報を取得できませんでした。</p>';
    }
}

document.addEventListener('DOMContentLoaded', fetchSapporoWeather);