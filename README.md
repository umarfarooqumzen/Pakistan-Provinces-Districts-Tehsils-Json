# 🇵🇰 Pakistan Administrative Divisions Dataset (JSON)

A complete and up-to-date dataset of **Pakistan’s administrative divisions** in clean **JSON format**, including **Provinces, Districts, and Tehsils**.  
This dataset is intended for use in **web development, research, GIS applications, and data analysis**.

---

## 📊 Dataset Overview
- **Provinces (including ICT)**: 7 + Islamabad Capital Territory  
- **Districts**: 167  
- **Tehsils**: ~657  

✔ Verified against official records and Wikipedia (2025).  
✔ Clean and consistent naming (Title Case).  
✔ JSON structured for easy use in applications.  

---

## 📂 Structure
The JSON file is structured as:

```json
{
  "provinces": [
    {
      "name": "Punjab",
      "districts": [
        {
          "name": "Lahore",
          "tehsils": [
            "Lahore City",
            "Lahore Cantt",
            "Model Town",
            "Raiwind",
            "Shalimar",
            "Wahga"
          ]
        }
      ]
    }
  ]
}
```
🚀 Usage Examples
1. Populate Dropdowns (Web Apps)
javascript
```
fetch("pakistan.json")
  .then(res => res.json())
  .then(data => {
    const provinces = data.provinces.map(p => p.name);
    console.log(provinces);
  });
```
2. Load into Python (Data Analysis)
python
```
import json

with open("pakistan.json", "r", encoding="utf-8") as f:
    data = json.load(f)

for province in data["provinces"]:
    print(province["name"], len(province["districts"]))
```
🔍 Applications
📌 Web forms: Populate province → district → tehsil cascading dropdowns.

🗺 GIS/Mapping: Overlay administrative boundaries with geospatial data.

📊 Research & Analysis: Use for demographic, economic, or policy studies.

⚙️ Software projects: Easily integrate into Laravel, Vue, React, Python, etc.

📝 Sources
Wikipedia: Administrative units of Pakistan

Government of Pakistan Statistics (verified where available)

🤝 Contributing
Contributions and corrections are welcome! Please open an issue or submit a pull request if you find discrepancies.

📜 License
This dataset is released under the MIT License.
You are free to use, modify, and distribute it with attribution.
