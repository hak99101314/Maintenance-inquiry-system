package com.ruiyang.model;

public class ReportData {
    private String label;
    private int value;

    // 建構子
    public ReportData(String label, int value) {
        this.label = label;
        this.value = value;
    }

    // Getter 和 Setter 方法
    public String getLabel() {
        return label;
    }

    public void setLabel(String label) {
        this.label = label;
    }

    public int getValue() {
        return value;
    }

    public void setValue(int value) {
        this.value = value;
    }

    @Override
    public String toString() {
        return "ReportData{" +
                "label='" + label + '\'' +
                ", value=" + value +
                '}';
    }
}
