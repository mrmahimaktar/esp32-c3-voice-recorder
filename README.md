# 🎙️ ESP32-C3 Voice Recorder & Messenger

An IoT voice recording and messaging device using ESP32-C3 Super Mini, INMP441 microphone, MAX98357A amplifier, and OLED display.

## ✨ Features

- 🔴 Record 10-second voice messages
- 🔊 Playback via speaker
- ☁️ Upload recordings to server
- 📩 Receive voice messages from server
- 🔁 Replay messages any time
- ✅ Mark as read when listened
- 📶 Smart multi-WiFi with internet check
- 🔋 Low power - WiFi ON only when needed
- 🖥️ OLED shows button instructions

## 🧰 Hardware Required

| Component | Quantity |
|-----------|----------|
| ESP32-C3 Super Mini | 1 |
| INMP441 I2S Microphone | 1 |
| MAX98357A Amplifier | 1 |
| SSD1306 OLED 128x64 | 1 |
| Push Button | 4 |
| Speaker (4Ω/8Ω) | 1 |
| TP4056 Charger (optional) | 1 |
| 3.7V LiPo Battery (optional) | 1 |

## 🔌 Wiring

| ESP32-C3 Pin | Connected To |
|--------------|--------------|
| GPIO 0 | INMP441 SD |
| GPIO 1 | MAX98357A DIN |
| GPIO 2 | INMP441 SCK + MAX98357A BCLK |
| GPIO 3 | INMP441 WS + MAX98357A LRC |
| GPIO 7 | Button B4 (Page Switch) |
| GPIO 8 | OLED SDA |
| GPIO 9 | OLED SCL |
| GPIO 10 | Button B1 (Record) |
| GPIO 20 | Button B2 (Play) |
| GPIO 21 | Button B3 (Upload) |
| 3V3 | All VDD/VIN pins |
| GND | All GND pins |

> **Note:** BCLK and LRC pins are shared between microphone and amplifier. They never run simultaneously (software controlled).

## 🎮 Button Functions

### Recording Page
| Button | Action |
|--------|--------|
| B1 | Record 10 seconds |
| B2 | Playback recording |
| B3 | Upload to server |
| B4 | Switch to Message Page |

### Message Page
| Button | Action |
|--------|--------|
| B1 | Play received message |
| B2 (hold 2s) | Mark as read |
| B4 | Switch to Recording Page |

## 📁 Project Structure
