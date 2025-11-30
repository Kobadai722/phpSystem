from flask import Flask, request, jsonify
from prophet import Prophet
import pandas as pd
import numpy as np

app = Flask(__name__)

# == 翌月売上予測と信頼度を返すAPI ==
@app.route('/predict_sales', methods=['POST'])
def predict_sales():
    data = request.json
    
    # 1. データフレームの整形
    try:
        # PHPから受け取ったJSONデータをDataFrameに変換
        df = pd.DataFrame(data)
        df['ds'] = pd.to_datetime(df['ds'])
        
        # 売上データ 'y' の型をfloatに変換
        df['y'] = df['y'].astype(float)
        
    except Exception as e:
        return jsonify({"success": False, "message": "データ形式が無効です"}), 400

    # 2. Prophetモデルの構築と学習
    # Prophetは季節性とトレンドを自動で検出
    model = Prophet(
        yearly_seasonality=True,  # 年単位の季節性を考慮
        weekly_seasonality=False, # (日次データなので) 週単位の季節性は不要と仮定
        daily_seasonality=False
    )
    model.fit(df)

    # 3. 翌月の日数を決定
    # 例: 予測開始日をデータセットの最終日の翌日に設定
    last_date = df['ds'].max()
    next_month_start = last_date + pd.Timedelta(days=1)
    
    # 翌月30日間の予測期間を生成
    future = model.make_future_dataframe(periods=30) 
    future = future[future['ds'] >= next_month_start]

    # 4. 予測の実行
    forecast = model.predict(future)

    # 5. 結果の集計
    # 翌月30日間の予測値 (yhat) と信頼区間 (yhat_lower, yhat_upper) を合計
    next_month_forecast = forecast['yhat'].sum()
    
    # 予測信頼区間の幅の平均を基に信頼度を計算
    # 信頼度 = 100% - (信頼区間の平均幅 / 予測値の平均) * 100
    avg_pred = forecast['yhat'].mean()
    avg_interval = (forecast['yhat_upper'] - forecast['yhat_lower']).mean()
    
    # 予測信頼度 (0～100%)
    confidence_rate = max(0, 100 - (avg_interval / avg_pred) * 100)
    
    return jsonify({
        "success": True,
        # 翌月売上予測 (整数に丸める)
        "next_month_forecast": int(round(next_month_forecast)),
        # 予測信頼度 (小数点以下1桁まで)
        "forecast_confidence": f"{confidence_rate:.1f}%",
        "raw_data": df.shape[0] # 学習データ件数の確認用
    })

if __name__ == '__main__':
    # 運用環境ではホストとポートを設定する必要があります
    app.run(debug=True, port=5000)