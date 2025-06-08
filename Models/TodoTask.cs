using System;

namespace TodoList.Models{
    public class TodoTask{
        public int Id {get; set;}
        public string Titulo { get; set; } = string.Empty;
        public string Usuario { get; set; }
        public bool Status {get; set;} = false;
        public DateTime CriadoEm {get; set;} = DateTime.Now;
        public DateTime? ConcluidoEm {get; set;} = null;
    }
}