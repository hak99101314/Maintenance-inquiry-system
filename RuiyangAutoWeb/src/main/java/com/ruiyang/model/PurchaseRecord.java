package com.ruiyang.model;

import java.sql.Date;

// 購買紀錄類別，用於儲存每筆進貨的詳細資訊
public class PurchaseRecord {
    private int purchaseId;         // 進貨記錄編號
    private String partName;        // 零件名稱
    private int quantity;           // 進貨數量
    private String supplier;        // 供應商名稱
    private Date purchaseDate; // 進貨日期
    private double price;           // 價格

    // 建構子，初始化所有進貨記錄屬性
    public PurchaseRecord(int purchaseId, String partName, int quantity, String supplier, Date date, double price) {
        System.out.println("Initializing PurchaseRecord object..."); // 初始化提示
        this.purchaseId = purchaseId;
        this.partName = partName;
        this.quantity = quantity;
        this.supplier = supplier;
        this.purchaseDate = date;
        this.price = price;
        System.out.println("PurchaseRecord initialized successfully."); // 初始化成功提示
    }

	// Getter 和 Setter 方法
    public int getPurchaseId() {
        System.out.println("Getting purchaseId..."); // 取得 purchaseId 提示
        return purchaseId;
    }

    public void setPurchaseId(int purchaseId) {
        System.out.println("Setting purchaseId to " + purchaseId); // 設定 purchaseId 提示
        this.purchaseId = purchaseId;
    }

    public String getPartName() {
        System.out.println("Getting partName..."); // 取得 partName 提示
        return partName;
    }

    public void setPartName(String partName) {
        System.out.println("Setting partName to " + partName); // 設定 partName 提示
        this.partName = partName;
    }

    public int getQuantity() {
        System.out.println("Getting quantity..."); // 取得 quantity 提示
        return quantity;
    }

    public void setQuantity(int quantity) {
        System.out.println("Setting quantity to " + quantity); // 設定 quantity 提示
        this.quantity = quantity;
    }

    public String getSupplier() {
        System.out.println("Getting supplier..."); // 取得 supplier 提示
        return supplier;
    }

    public void setSupplier(String supplier) {
        System.out.println("Setting supplier to " + supplier); // 設定 supplier 提示
        this.supplier = supplier;
    }

    public Date getPurchaseDate() {
        System.out.println("Getting purchaseDate..."); // 取得 purchaseDate 提示
        return purchaseDate;
    }

    public void setPurchaseDate(Date purchaseDate) {
        System.out.println("Setting purchaseDate to " + purchaseDate); // 設定 purchaseDate 提示
        this.purchaseDate = purchaseDate;
    }

    public double getPrice() {
        System.out.println("Getting price..."); // 取得 price 提示
        return price;
    }

    public void setPrice(double price) {
        System.out.println("Setting price to " + price); // 設定 price 提示
        this.price = price;
    }

    // 將進貨記錄轉為字串顯示，方便檢視紀錄內容
    @Override
    public String toString() {
        System.out.println("Converting PurchaseRecord to string..."); // 轉換為字串提示
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
