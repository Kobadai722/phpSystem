import pandas as pd
import numpy as np
from prophet import Prophet
from flask import Flask, request, jsonify
from flask_cors import CORS # ★CORS対策のために追加★

# Flaskアプリケーションの初期化
app = Flask(__name__)
# 開発環境で異なるオリジンからのアクセスを許可 (CORSエラー対策)
CORS(app) 

# Propht予測を実行するエンドポイント
@app.route('/predict_sales', methods=['POST'])
def predict_sales():
    """
    POSTリクエストで過去の売上データ（ds, yのリスト）を受け取り、
    翌月の売上予測を返します。
    """
    try:
        data = request.json
        if not data or not isinstance(data, list):
            return jsonify({'success': False, 'message': '無効なデータ形式です。'}), 400

        # JSONデータをDataFrameに変換
        df = pd.DataFrame(data)
        
        # 必須カラムのチェック
        if 'ds' not in df.columns or 'y' not in df.columns:
            return jsonify({'success': False, 'message': 'データにはds（日付）とy（売上）カラムが必要です。'}), 400

        # データ型を適切に変換
        df['ds'] = pd.to_datetime(df['ds'])
        df['y'] = pd.to_numeric(df['y'])
        
        # Prophetモデルを初期化し、学習させる (日本の特性を考慮)
        model = Prophet(
            yearly_seasonality=True,
            weekly_seasonality=True,
            daily_seasonality=False,
            seasonality_mode='multiplicative' # 乗法型（売上が成長傾向にあるため）
        )
        # 日本の祝日を考慮 (オプション)
        model.add_country_holidays(country_name='JP') 
        
        model.fit(df)

        # 翌月の日数を決定 (例: 30日間を予測)
        future_periods = 30
        
        # 将来のフレームワークを作成
        future = model.make_future_dataframe(periods=future_periods)

        # 予測の実行
        forecast = model.predict(future)
        
        # 翌月全体の予測値を抽出
        # 予測期間のyhat（予測値）の平均を翌月の予測値とする
        next_month_forecast_df = forecast.tail(future_periods)
        
        # yhat（予測値）を合計する
        next_month_forecast = next_month_forecast_df['yhat'].sum()
        
        # 予測の信頼度を計算 (yhat_upper と yhat_lower の差の平均から簡易的に算出)
        # 信頼度 = 100 * (1 - (yhat_upper - yhat_lower)の平均 / yhatの平均)
        mean_yhat = next_month_forecast_df['yhat'].mean()
        mean_interval = (next_month_forecast_df['yhat_upper'] - next_month_forecast_df['yhat_lower']).mean()
        
        # 平均予測値がゼロに近い場合は、信頼度を算出せずに「---」とする
        if mean_yhat == 0:
            confidence = 0
        else:
            # 信頼度を0%から100%の間に収める
            confidence = max(0, 100 * (1 - (mean_interval / mean_yhat)))


        # 結果をJSONで返す
        return jsonify({
            'success': True,
            'next_month_forecast': round(next_month_forecast), # 予測値は整数に丸める
            'forecast_confidence': f"{round(confidence)}%", # 信頼度はパーセンテージ形式
            'message': '予測が完了しました。'
        })

    except Exception as e:
        print(f"予測処理中にエラーが発生しました: {e}")
        # エラーメッセージを返す
        return jsonify({'success': False, 'message': f"内部処理エラー: {str(e)}"}), 500

if __name__ == '__main__':
    # デバッグモードとポートを指定してFlaskアプリを起動
    app.run(debug=True, port=5000)