package com.ruiyang.model;

public class Inventory {
    private int partId;          // 零件編號
    private String partName;     // 零件名稱
    private int quantity;        // 數量

    // 建構子
    public Inventory(int partId, String partName, int quantity) {
        this.partId = partId;
        this.partName = partName;
        this.quantity = quantity;
    }

    // Getter 和 Setter 方法

    public int getPartId() {
        return partId;
    }

    public void setPartId(int partId) {
        this.partId = partId;
    }

    public String getPartName() {
        return partName;
    }

    public void setPartName(String partName) {
        this.partName = partName;
    }

    public int getQuantity() {
        return quantity;
    }

    public void setQuantity(int quantity) {
        this.quantity = quantity;
    }

    @Override
    public String toString() {
        return "Inventory{" +
                "partId=" + partId +
                ", partName='" + partName + '\'' +
                ", quantity=" + quantity +
                '}';
    }
}
