###  TodoList (Gerenciador de tarefas)
    # Estrutura do projeto

        TodoList/
        ├── Models/
        │   └── TodoTask.cs
        ├── Routes/
        │   └── RotasGet.cs
        ├── Program.cs


    ### Classes utilizadas

        ## TodoTask - Modelo de dados da tarefa.

            public class TodoTask{
                public int Id {get; set;}
                public string Titulo { get; set; } = string.Empty;
                public bool Status {get; set;} = false;
                public DateTime CriadoEm {get; set;} = DateTime.Now;
                public DateTime? ConcluidoEm {get; set;} = null;
            }


    ## GetTasksController.cs
        Classe estática que define as rotas GET da API, incluindo as rotas para listar todas as tarefas e buscar tarefa por id, formatando as datas de criação e conclusão para o formato "dd/MM/yyyy HH:mm".

        # Rotas implementadas
            GET "/" - Retorna mensagem confirmando funcionamento da API.

            GET "/entrar" - Exibe instruções para uso das rotas.

            GET "/entrar/usuario/tarefas" - Retorna lista de tarefas com datas formatadas.

            GET "/entrar/usuario/tarefas/{id}" - Retorna tarefa específica formatada ou erro caso não exista.

    ## Inicialização da aplicação

        # Program.cs
            var builder = WebApplication.CreateBuilder(args);
            var app = builder.Build();

            app.MapGetRotas(); // adiciona as rotas que criamos

            app.Run();
