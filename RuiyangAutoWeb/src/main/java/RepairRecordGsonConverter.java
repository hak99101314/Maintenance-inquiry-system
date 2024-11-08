import com.google.gson.Gson;
import com.google.gson.JsonSyntaxException;

class RepairRecord {
    private String id;
    private String description;
    private double cost;

    public RepairRecord(String id, String description, double cost) {
        this.id = id;
        this.description = description;
        this.cost = cost;
    }

    // Getters and Setters
    public String getId() { return id; }
    public void setId(String id) { this.id = id; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public double getCost() { return cost; }
    public void setCost(double cost) { this.cost = cost; }

    @Override
    public String toString() {
        return "RepairRecord{" +
                "id='" + id + '\'' +
                ", description='" + description + '\'' +
                ", cost=" + cost +
                '}';
    }
}

public class RepairRecordGsonConverter {
    
    private final Gson gson;

    public RepairRecordGsonConverter() {
        this.gson = new Gson();
    }

    public String convertToJson(RepairRecord record) {
        return gson.toJson(record);
    }

    public RepairRecord convertFromJson(String jsonString) {
        try {
            return gson.fromJson(jsonString, RepairRecord.class);
        } catch (JsonSyntaxException e) {
            System.out.println("JSON 格式錯誤：" + e.getMessage());
            return null;
        }
    }

    public static void main(String[] args) {
        var converter = new RepairRecordGsonConverter();
        
        var record = new RepairRecord("001", "Engine repair", 250.75);
        
        var json = converter.convertToJson(record);
        System.out.println("序列化為 JSON: " + json);
        
        var deserializedRecord = converter.convertFromJson(json);
        System.out.println("反序列化回 RepairRecord 對象: " + deserializedRecord);
    }
}
