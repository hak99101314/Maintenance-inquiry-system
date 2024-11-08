package com.ruiyang.model;

import java.time.LocalDate;

// Report 類別：表示維修保養報表
public class Report {
    private int reportId;          // 報表編號 (唯一識別報表的 ID)
    private int maintenanceRecordId; // 維修保養紀錄編號 (連結到特定維修紀錄)
    private int employeeId;        // 員工編號 (負責此報表的員工 ID)
    private String licensePlate;   // 車牌號碼 (報表所屬車輛的車牌)
    private LocalDate reportDate;  // 報表日期 (生成此報表的日期)
    private double partsCost;      // 零件費用 (使用零件的總成本)
    private double quotedAmount;   // 報價金額 (報表中的總報價金額)

    // 建構子：初始化 Report 類別的所有屬性
    public Report(int reportId, int maintenanceRecordId, int employeeId, String licensePlate,
                  LocalDate reportDate, double partsCost, double quotedAmount) {
        System.out.println("初始化 Report 物件...");
        this.reportId = reportId;
        this.maintenanceRecordId = maintenanceRecordId;
        this.employeeId = employeeId;
        this.licensePlate = licensePlate;
        this.reportDate = reportDate;
        this.partsCost = partsCost;
        this.quotedAmount = quotedAmount;
        System.out.println("Report 物件初始化完成");
    }

    // Getter 和 Setter 方法：用於存取和修改各個屬性

    public int getReportId() {
        System.out.println("取得 reportId...");
        return reportId;
    }

    public void setReportId(int reportId) {
        System.out.println("設定 reportId 為 " + reportId);
        this.reportId = reportId;
    }

    public int getMaintenanceRecordId() {
        System.out.println("取得 maintenanceRecordId...");
        return maintenanceRecordId;
    }

    public void setMaintenanceRecordId(int maintenanceRecordId) {
        System.out.println("設定 maintenanceRecordId 為 " + maintenanceRecordId);
        this.maintenanceRecordId = maintenanceRecordId;
    }

    public int getEmployeeId() {
        System.out.println("取得 employeeId...");
        return employeeId;
    }

    public void setEmployeeId(int employeeId) {
        System.out.println("設定 employeeId 為 " + employeeId);
        this.employeeId = employeeId;
    }

    public String getLicensePlate() {
        System.out.println("取得 licensePlate...");
        return licensePlate;
    }

    public void setLicensePlate(String licensePlate) {
        System.out.println("設定 licensePlate 為 " + licensePlate);
        this.licensePlate = licensePlate;
    }

    public LocalDate getReportDate() {
        System.out.println("取得 reportDate...");
        return reportDate;
    }

    public void setReportDate(LocalDate reportDate) {
        System.out.println("設定 reportDate 為 " + reportDate);
        this.reportDate = reportDate;
    }

    public double getPartsCost() {
        System.out.println("取得 partsCost...");
        return partsCost;
    }

    public void setPartsCost(double partsCost) {
        System.out.println("設定 partsCost 為 " + partsCost);
        this.partsCost = partsCost;
    }

    public double getQuotedAmount() {
        System.out.println("取得 quotedAmount...");
        return quotedAmount;
    }

    public void setQuotedAmount(double quotedAmount) {
        System.out.println("設定 quotedAmount 為 " + quotedAmount);
        this.quotedAmount = quotedAmount;
    }

    // toString 方法：返回包含報表所有資訊的字串
    @Override
    public String toString() {
        System.out.println("生成 Report 物件的字串表示...");
        return "Report{" +
                "reportId=" + reportId +
                ", maintenanceRecordId=" + maintenanceRecordId +
                ", employeeId=" + employeeId +
                ", licensePlate='" + licensePlate + '\'' +
                ", reportDate=" + reportDate +
                ", partsCost=" + partsCost +
                ", quotedAmount=" + quotedAmount +
                '}';
    }
}
