import sys
import json
import pandas as pd
from prophet import Prophet

# ✅ Read from file if provided, else stdin
if len(sys.argv) > 1:
    with open(sys.argv[1], 'r') as f:
        input_data = json.load(f)
else:
    raw = sys.stdin.read().strip()
    if not raw:
        raise ValueError("No input received")
    input_data = json.loads(raw)

df = pd.DataFrame(input_data)

# Prophet format
df.columns = ['ds', 'y']
df['ds'] = pd.to_datetime(df['ds'])

model = Prophet(daily_seasonality=True)
model.fit(df)

future = model.make_future_dataframe(periods=1)
forecast = model.predict(future)

result = forecast[['ds', 'yhat']].tail(1)

print(json.dumps({
    "date": str(result.iloc[0]['ds']),
    "prediction": float(result.iloc[0]['yhat'])
}))