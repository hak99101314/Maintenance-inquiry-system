package com.ruiyang.model;

import java.sql.Date;
import java.time.LocalDate;

public class Quotation {
    private int quotationId;
    private int inquiryId;
    private double quotedAmount;
    private int employeeId;
    private LocalDate quotationDate;

    // 私有建構子，僅通過工廠方法創建實例
    public Quotation(int quotationId, int inquiryId, double quotedAmount, int employeeId, LocalDate date) {
        this.quotationId = quotationId;
        this.inquiryId = inquiryId;
        this.quotedAmount = quotedAmount;
        this.employeeId = employeeId;
        this.quotationDate = date;
    }

    public Quotation(int int1, int int2, double double1, int int3, Date date) {
		// TODO Auto-generated constructor stub
	}

	// 工廠方法：使用 LocalDate 創建 Quotation
    public static Quotation fromLocalDate(int quotationId, int inquiryId, double quotedAmount, int employeeId, LocalDate quotationDate) {
        return new Quotation(quotationId, inquiryId, quotedAmount, employeeId, quotationDate);
    }

    // 工廠方法：使用 java.sql.Date 創建 Quotation
    public static Quotation fromSqlDate(int quotationId, int inquiryId, double quotedAmount, int employeeId, Date quotationDate) {
        LocalDate localDate = (quotationDate != null) ? quotationDate.toLocalDate() : null;
        return new Quotation(quotationId, inquiryId, quotedAmount, employeeId, localDate);
    }

    // Getter 和 Setter 方法
    public int getQuotationId() {
        return quotationId;
    }

    public void setQuotationId(int quotationId) {
        this.quotationId = quotationId;
    }

    public int getInquiryId() {
        return inquiryId;
    }

    public void setInquiryId(int inquiryId) {
        this.inquiryId = inquiryId;
    }

    public double getQuotedAmount() {
        return quotedAmount;
    }

    public void setQuotedAmount(double quotedAmount) {
        this.quotedAmount = quotedAmount;
    }

    public int getEmployeeId() {
        return employeeId;
    }

    public void setEmployeeId(int employeeId) {
        this.employeeId = employeeId;
    }

    public LocalDate getQuotationDate() {
        return quotationDate;
    }

    public void setQuotationDate(LocalDate quotationDate) {
        this.quotationDate = quotationDate;
    }

    @Override
    public String toString() {
        return "Quotation{" +
                "quotationId=" + quotationId +
                ", inquiryId=" + inquiryId +
                ", quotedAmount=" + quotedAmount +
                ", employeeId=" + employeeId +
                ", quotationDate=" + quotationDate +
                '}';
    }
}
