# TodoList (Gerenciador de tarefas)

### Estrutura do projeto MVC

    TodoApp/
    ├── Controllers/ (Métodos)
    │   └── GetTasksController.cs 
    │   └── PostTaskController.cs
    │   └── DeleteTaskController.cs
    │
    ├── Models/ (Entidade)
    │   └── TodoTask.cs
    │
    ├── Data/ (Banco de dados)
    │   └── AppDbContext.cs
    │
    ├── Program.cs
    ├── TodoApp.

A estrutura descrita acima não está considerando as pastas criadas com o comando `dotnet new webapi`, apenas a estrutura considerando MVC e BD.

### Pacotes necessários  <!-- Adicionem aqui os pacotes necessários para o projeto<>

**SQlite e Entity**

    dotnet add package Microsoft.EntityFrameworkCore.Sqlite
    dotnet add package Microsoft.EntityFrameworkCore.Tools

### Classes utilizadas

**TodoTask** - Modelo de dados da tarefa.

**AppDbContext** - Contexto do Entity Framework para conectar com o banco SQLite.

**[metodo]TasksController** - Controller com os métodos GET, POST e DELETE.

### Banco de dados

    using Microsoft.EntityFrameworkCore;
    using TodoList.Models;
    
    namespace TodoList.Data{
        public class AppDbContext : DbContext {
            public AppDbContext(DbContextOptions<AppDbContext> options) : base(options) { }
            public DbSet<TodoTask> Tasks { get; set; }
        }
    }

`public class AppDbContext : AppDbContext` Herda da classe `DbContext`, que é a classe base do EF Core usada para interagir com o banco de dados.

`using Microsoft.EntityFrameworkCore;` Importa os recursos principais do Entity Framework Core, como `DbContext, DbSet, DbContextOptions` etc.

`public AppDbContext(DbContextOptions<appDbContext> options) : base(options) { }` Cronstrutor da classe que recebe configuração de contexto

*Propridades DbSet* 
`public DbSet<TodoTask> Tasks { get; set; }`
`DbSet<T>` representa uma tabela no banco de dados.
`TodoTask` é a entidade que mapeia os dados de uma tarefa da lista.
`Tasks` será a tabela de tarefas no banco.

**Entidade**

    namespace TodoList.Models{
        public class TodoTask{
            public int Id {get; set;}
            public string Titulo {get; set;}
            public bool Status {get; set;} = false;
            public DateTime CriadoEm {get; set;} = DateTime.Now;
            public DateTime? ConcluidoEm {get; set;} = null;
        }
    }

**Atributos da tabela**
- Id
- Titulo
- Status
- CriadoEm
- ConcluidoEm

### Inicialização da aplicação 
**`Program.cs`**

1. Criar o builder da aplicação
    `var builder = WebApplication.CreateBuilder(args);`

Inicializa a configuração da aplicação e os serviços.

2. Configurar o banco de dados (SQLite + EF Core)

        builder.Services.AddDbContext<AppDbContext>(options =>
            options.UseSqlite("Data Source=TodoList.db"));

- Registra o `AppDbContext` como serviço.
- Define o SQLite como banco de dados.
- Usa um arquivo local chamado `TodoList.db`.

3. Habilitar Controllers (API)

        builder.Services.AddControllers();

- Permite o uso de Controllers para criar endpoints RESTful.

4. Construir a aplicação

        var app = builder.Build();
- Monta a aplicação com os serviços e configurações feitas.

5. Mapear rotas para os Controllers

        app.MapControllers();

- Roteia as URLs HTTP para os métodos nos Controllers.

6. Executa a aplicação

        app.Run();

- Inicia o servidor web
- Começa a receber requisições HTTP.