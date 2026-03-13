<?php
class TableEntity
{
    protected PDO $db;
    protected string $tableName;
    protected ?string $lastSQL = null;

    public function __construct(PDO $db, string $tableName)
    {
        $this->db = $db;
        $this->tableName = $tableName;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getLastSQL(): ?string
    {
        return $this->lastSQL;
    }

    protected function run(string $sql, array $params = []): PDOStatement
    {
        $this->lastSQL = $sql;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function selectAll(): array
    {
        $stmt = $this->run("SELECT * FROM {$this->tableName}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}