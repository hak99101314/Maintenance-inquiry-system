package com.ruiyang.model;

import java.time.LocalDate;

public class PurchaseRecord {
    private int purchaseId;         // 進貨記錄編號
    private String partName;        // 零件名稱
    private int quantity;           // 數量
    private String supplier;        // 供應商名稱
    private LocalDate purchaseDate; // 進貨日期
    private double price;           // 價格

    // 建構子
    public PurchaseRecord(int purchaseId, String partName, int quantity, String supplier, LocalDate purchaseDate, double price) {
        this.purchaseId = purchaseId;
        this.partName = partName;
        this.quantity = quantity;
        this.supplier = supplier;
        this.purchaseDate = purchaseDate;
        this.price = price;
    }

    // Getter 和 Setter 方法

    public int getPurchaseId() {
        return purchaseId;
    }

    public void setPurchaseId(int purchaseId) {
        this.purchaseId = purchaseId;
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

    public String getSupplier() {
        return supplier;
    }

    public void setSupplier(String supplier) {
        this.supplier = supplier;
    }

    public LocalDate getPurchaseDate() {
        return purchaseDate;
    }

    public void setPurchaseDate(LocalDate purchaseDate) {
        this.purchaseDate = purchaseDate;
    }

    public double getPrice() {
        return price;
    }

    public void setPrice(double price) {
        this.price = price;
    }

    @Override
    public String toString() {
        return "PurchaseRecord{" +
                "purchaseId=" + purchaseId +
                ", partName='" + partName + '\'' +
                ", quantity=" + quantity +
                ", supplier='" + supplier + '\'' +
                ", purchaseDate=" + purchaseDate +
                ", price=" + price +
                '}';
    }
}
