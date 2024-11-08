package com.ruiyang.model;

import java.sql.Date;

// Inquiry 類別，表示詢價單的各項資料
public class Inquiry {
    private int inquiryId; // 詢價單 ID
    private int memberId; // 會員 ID
    private int customerId; // 客戶 ID
    private int employeeId; // 員工 ID
    private int rating; // 客戶評分
    private String feedback; // 客戶回饋
    private Date inquiryDate; // 詢價日期，使用 LocalDate 表示
    private String productName; // 產品名稱
    private int quantity; // 產品數量
    private String customerContact; // 客戶聯絡資訊
    private int userId; // 用戶 ID
    private String status; // 詢價單狀態

    // 全部屬性建構子，用於查詢詳細詢價單資料
    public Inquiry(int inquiryId, int memberId, int customerId, int employeeId, int rating, String feedback,
                   Date inquiryDate) {
        this.inquiryId = inquiryId;
        this.memberId = memberId;
        this.customerId = customerId;
        this.employeeId = employeeId;
        this.rating = rating;
        this.feedback = feedback;
        this.inquiryDate = inquiryDate;
        System.out.println("建構子 Inquiry(int, int, int, int, int, String, LocalDate) 已執行，初始化成功");
    }

    // 詢價建構子，包含產品資訊，用於查詢詢價產品資料
    public Inquiry(int inquiryId, int userId, String productName, int quantity, Date date, String status) {
        this.inquiryId = inquiryId;
        this.userId = userId;
        this.productName = productName;
        this.quantity = quantity;
        this.inquiryDate = date;
        this.status = status;
        System.out.println("建構子 Inquiry(int, int, String, int, LocalDate, String) 已執行，初始化成功");
    }

    // Getter 和 Setter 方法，用於存取和修改類別屬性

   
	public int getInquiryId() {
        System.out.println("getInquiryId() 已執行");
        return inquiryId;
    }

    public void setInquiryId(int inquiryId) {
        this.inquiryId = inquiryId;
        System.out.println("setInquiryId() 已執行，inquiryId 已設定為 " + inquiryId);
    }

    public int getMemberId() {
        System.out.println("getMemberId() 已執行");
        return memberId;
    }

    public void setMemberId(int memberId) {
        this.memberId = memberId;
        System.out.println("setMemberId() 已執行，memberId 已設定為 " + memberId);
    }

    public int getCustomerId() {
        System.out.println("getCustomerId() 已執行");
        return customerId;
    }

    public void setCustomerId(int customerId) {
        this.customerId = customerId;
        System.out.println("setCustomerId() 已執行，customerId 已設定為 " + customerId);
    }

    public int getEmployeeId() {
        System.out.println("getEmployeeId() 已執行");
        return employeeId;
    }

    public void setEmployeeId(int employeeId) {
        this.employeeId = employeeId;
        System.out.println("setEmployeeId() 已執行，employeeId 已設定為 " + employeeId);
    }

    public int getRating() {
        System.out.println("getRating() 已執行");
        return rating;
    }

    public void setRating(int rating) {
        this.rating = rating;
        System.out.println("setRating() 已執行，rating 已設定為 " + rating);
    }

    public String getFeedback() {
        System.out.println("getFeedback() 已執行");
        return feedback;
    }

    public void setFeedback(String feedback) {
        this.feedback = feedback;
        System.out.println("setFeedback() 已執行，feedback 已設定為 " + feedback);
    }

    public Date getInquiryDate() {
        System.out.println("getInquiryDate() 已執行");
        return inquiryDate;
    }

    public void setInquiryDate(Date inquiryDate) {
        this.inquiryDate = inquiryDate;
        System.out.println("setInquiryDate() 已執行，inquiryDate 已設定為 " + inquiryDate);
    }

    public String getProductName() {
        System.out.println("getProductName() 已執行");
        return productName;
    }

    public void setProductName(String productName) {
        this.productName = productName;
        System.out.println("setProductName() 已執行，productName 已設定為 " + productName);
    }

    public int getQuantity() {
        System.out.println("getQuantity() 已執行");
        return quantity;
    }

    public void setQuantity(int quantity) {
        this.quantity = quantity;
        System.out.println("setQuantity() 已執行，quantity 已設定為 " + quantity);
    }

    public String getCustomerContact() {
        System.out.println("getCustomerContact() 已執行");
        return customerContact;
    }

    public void setCustomerContact(String customerContact) {
        this.customerContact = customerContact;
        System.out.println("setCustomerContact() 已執行，customerContact 已設定為 " + customerContact);
    }

    public int getUserId() {
        System.out.println("getUserId() 已執行");
        return userId;
    }

    public void setUserId(int userId) {
        this.userId = userId;
        System.out.println("setUserId() 已執行，userId 已設定為 " + userId);
    }

    public String getStatus() {
        System.out.println("getStatus() 已執行");
        return status;
    }

    public void setStatus(String status) {
        this.status = status;
        System.out.println("setStatus() 已執行，status 已設定為 " + status);
    }
}
