package com.ruiyang.model;

import java.time.LocalDate;

public class Report {
    private int reportId;          // 報表編號
    private int maintenanceRecordId; // 維修保養紀錄編號
    private int employeeId;        // 員工編號
    private String licensePlate;   // 車牌號碼
    private LocalDate reportDate;  // 報表日期
    private double partsCost;      // 零件費用
    private double quotedAmount;   // 報價金額

    // 建構子
    public Report(int reportId, int maintenanceRecordId, int employeeId, String licensePlate,
                  LocalDate reportDate, double partsCost, double quotedAmount) {
        this.reportId = reportId;
        this.maintenanceRecordId = maintenanceRecordId;
        this.employeeId = employeeId;
        this.licensePlate = licensePlate;
        this.reportDate = reportDate;
        this.partsCost = partsCost;
        this.quotedAmount = quotedAmount;
    }

    // Getter 和 Setter 方法

    public int getReportId() {
        return reportId;
    }

    public void setReportId(int reportId) {
        this.reportId = reportId;
    }

    public int getMaintenanceRecordId() {
        return maintenanceRecordId;
    }

    public void setMaintenanceRecordId(int maintenanceRecordId) {
        this.maintenanceRecordId = maintenanceRecordId;
    }

    public int getEmployeeId() {
        return employeeId;
    }

    public void setEmployeeId(int employeeId) {
        this.employeeId = employeeId;
    }

    public String getLicensePlate() {
        return licensePlate;
    }

    public void setLicensePlate(String licensePlate) {
        this.licensePlate = licensePlate;
    }

    public LocalDate getReportDate() {
        return reportDate;
    }

    public void setReportDate(LocalDate reportDate) {
        this.reportDate = reportDate;
    }

    public double getPartsCost() {
        return partsCost;
    }

    public void setPartsCost(double partsCost) {
        this.partsCost = partsCost;
    }

    public double getQuotedAmount() {
        return quotedAmount;
    }

    public void setQuotedAmount(double quotedAmount) {
        this.quotedAmount = quotedAmount;
    }

    @Override
    public String toString() {
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
