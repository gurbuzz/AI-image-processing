# yolo_analyze.py
import logging
from PIL import Image
import json
from ultralytics import YOLO

# Log yapılandırması (Konsola log yazma)
logging.basicConfig(
    level=logging.INFO,  
    format='%(asctime)s - %(levelname)s - %(message)s',  
    datefmt='%Y-%m-%d %H:%M:%S',
    handlers=[
        logging.StreamHandler()  # Konsola log yaz
    ]
)

def analyze_image(image_path):
    try:
        # Görüntüyü yükle
        image = Image.open(image_path).convert("RGB")
        logging.info(f"Görüntü boyutu: {image.size}, format: {image.mode}")

        # YOLOv8 modelini kendi eğittiğin modelle yükle
        model = YOLO("C:/ai-image-processing/runs/detect/train3/weights/best.pt")  # Kendi modelinin yolunu kullan
        logging.info("YOLOv8 modeli başarıyla yüklendi.")

        # Görüntüyü işleyip analiz et - confidence threshold'u ayarla
        results = model(image_path, conf=0.2)  # Güvenlik eşiğini 0.2 yaparak daha fazla tahmin alabilirsin
        logging.info(f"Model çıktı sonuçları: {results}")

        # Sonuçları işleme
        detections = []
        if results:
            for result in results:
                boxes = result.boxes  # Bu sonuçtaki kutuları al
                if boxes is not None:
                    for box in boxes:
                        label = model.names[int(box.cls)]  # Sınıf adını al
                        score = float(box.conf)  # Güven skorunu al
                        
                        # Koordinatları listeye çevir
                        coords = box.xyxy.tolist() if hasattr(box, 'xyxy') else []
                        
                        # Kontrol et ve ekle
                        if coords:
                            detections.append({
                                "label": label,
                                "score": score,
                                "box": coords
                            })
                        else:
                            logging.warning("Boş veya geçersiz koordinatlar.")
                else:
                    logging.warning("Algılanan kutu yok.")
        
            if detections:
                logging.info(f"Algılanan nesneler: {detections}")
            else:
                logging.warning("Algılanan nesne yok.")
                return json.dumps({"message": "Algılanan nesne yok."})

        # Sonuçları JSON formatına dönüştür
        return json.dumps(detections, ensure_ascii=False)

    except Exception as e:
        logging.error(f"Bir hata oluştu: {str(e)}")
        return json.dumps({"error": str(e)}, ensure_ascii=False)


if __name__ == "__main__":
    import sys
    if len(sys.argv) > 1:
        image_path = sys.argv[1]  # Laravel'den görüntü yolunu argüman olarak al
        print(analyze_image(image_path))
    else:
        logging.error("Görüntü yolu sağlanmadı.")
