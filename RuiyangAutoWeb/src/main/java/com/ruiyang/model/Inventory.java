package com.ruiyang.model;

// Inventory 類別，用於管理庫存物件的基本資料
public class Inventory {
    private int partId;          // 零件編號，用來唯一標識每個零件
    private String partName;     // 零件名稱，顯示零件的名稱
    private int quantity;        // 數量，表示零件的庫存數量

    // 建構子，用於初始化 Inventory 物件的屬性
    public Inventory(int partId, String partName, int quantity) {
        System.out.println("初始化 Inventory 物件...");
        this.partId = partId;        // 設定零件編號
        this.partName = partName;    // 設定零件名稱
        this.quantity = quantity;    // 設定庫存數量
        System.out.println("初始化完成，零件編號：" + partId + "，零件名稱：" + partName + "，庫存數量：" + quantity);
    }

    // Getter 方法，獲取零件編號
    public int getPartId() {
        System.out.println("取得零件編號：" + partId);
        return partId;
    }

    // Setter 方法，設定零件編號
    public void setPartId(int partId) {
        System.out.println("設定零件編號為：" + partId);
        this.partId = partId;
    }

    // Getter 方法，獲取零件名稱
    public String getPartName() {
        System.out.println("取得零件名稱：" + partName);
        return partName;
    }

    // Setter 方法，設定零件名稱
    public void setPartName(String partName) {
        System.out.println("設定零件名稱為：" + partName);
        this.partName = partName;
    }

    // Getter 方法，獲取庫存數量
    public int getQuantity() {
        System.out.println("取得庫存數量：" + quantity);
        return quantity;
    }

    // Setter 方法，設定庫存數量
    public void setQuantity(int quantity) {
        System.out.println("設定庫存數量為：" + quantity);
        this.quantity = quantity;
    }

    // toString 方法，返回物件的字串表示，用於顯示 Inventory 資訊
    @Override
    public String toString() {
        String inventoryInfo = "Inventory{" +
                "partId=" + partId +
                ", partName='" + partName + '\'' +
                ", quantity=" + quantity +
                '}';
        System.out.println("轉換為字串：" + inventoryInfo);
        return inventoryInfo;
    }
}
