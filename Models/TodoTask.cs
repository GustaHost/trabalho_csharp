using System;
using System.Text.Json.Serialization; // Adicione esta linha

namespace TodoList.Models
{
    // NOVO: Definição do Enum de Prioridade
    [JsonConverter(typeof(JsonStringEnumConverter))] // Garante que o enum seja serializado como string ("Baixa", "Media", "Alta")
    public enum Prioridade
    {
        Baixa,
        Media, // Use "Media" sem acento para corresponder mais facilmente ao JSON
        Alta
    }

    public class TodoTask
    {
        public int Id { get; set; }
        public string Titulo { get; set; } = string.Empty;
        public string Usuario { get; set; }
        public bool Status { get; set; } = false;
        public decimal Preco { get; set; }

        // NOVO: Adicione a propriedade Prioridade ao seu modelo TodoTask
        public Prioridade Prioridade { get; set; } = Prioridade.Media; // Valor padrão como "Media"

        public DateTime CriadoEm { get; set; } = DateTime.Now;
        public DateTime? ConcluidoEm { get; set; } = null;
    }
}