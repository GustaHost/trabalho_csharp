# TodoList (Delete Task)

### Estrutura do código

    using System;
    using System.Linq;
    using TodoList.Data;

    namespace TodoList.Controllers
    {
        public class DeleteTarefaController
        {
            public bool ApagarTarefa(int id, string tipoUsuario, string nomeUsuario)
            {
                var tarefa = BancoDados.Tarefas.FirstOrDefault(t => t.Id == id);

                if (tarefa == null)
                    return false;

            // Admin pode apagar qualquer tarefa
                if (tipoUsuario == "admin" || tarefa.Usuario == nomeUsuario)
                {
                    BancoDados.Tarefas.Remove(tarefa);
                    BancoDados.SalvarDados();
                    return true;
                }

                return false;
            }
        }
    }

### Função

 O método DELETE em uma API serve para apagar algo do sistema. No caso de uma lista de tarefas, ele exclui uma tarefa específica do banco de dados. Quando o cliente envia uma requisição DELETE, ele informa o ID da tarefa que quer apagar. Por exemplo: `DELETE /api/tasks/3`. A API procura essa tarefa. Se não achar, responde que ela não existe. Se encontrar, remove do banco e confirma que deu certo. Esse método faz parte do básico de uma API RESTful, junto com GET, POST e PUT. Ele garante que dados possam ser excluídos com clareza e segurança.

### Método DELETE

 Dentro da pasta **Controllers*.

1. **Using**

        using System;
        using System.Linq;
        using TodoList.Data;
   

`System`: para funcionalidades básicas.

`System.Linq`: para usar o método `FirstOrDefault`.

`TodoList.Data`: para acessar a "base de dados" (BancoDados).


2. **A classe DeleteTarefaController**

          public class DeleteTarefaController
        {
   

 Único método público chamado `ApagarTarefa`, responsável por localizar uma tarefa por ID e, se for permitido, remover ela da lista de tarefas.


3. **Método de extensão para o WebApplication:**

          public bool ApagarTarefa(int id, string tipoUsuario, string nomeUsuario)
   

 Este método está recebendo 3 parâmetros: 
- `int id`: o ID da tarefa que queremos apagar.
- `string `tipoUsuario`: o tipo de usuário logado ("admin" ou "usuário").
- `string nomeUsuario`: o nome do usuário logado.

  
4. **var tarefa = BancoDados.Tarefas.FirstOrDefault**

         app.MapDelete("/tarefas/{id}", async (int id, AppDbContext context) =>
        {
   

- Procura na lista de tarefas (`BancoDados.Tarefas`) uma tarefa que tenha o mesmo ID passado como parâmetro.
- Se encontrar -> guarda na variável `tarefa`.
- Se **não encontrar** -> `tarefa` fica null.
- `FirstOrDefault` - > pega o **primeiro item que corresponde à condição, ou null se não tiver. 


5. **Csharp**

       if (tarefa == null)
           return false;

- Se não encontrou nenhuma tarefa com esse ID, ele já retorna `false`.
- Isso significa que não é possível apagar uma tarefa que não existe.
  

 Aqui ele verifica se o usuário que quer apagar tem permissão:

        if (tipoUsuario == "admin" || tarefa.Usuario == nomeUsuario)

- Se for um "admin" pode apagar qualquer tarefa.
- Se for o dono da tarefa (ou seja, o 'Usuario' da tarefa for igual ao 'nomeUsuario' informado.)


 Se qualquer uma for verdadeira, ele entra no bloco 'if'.

    BancoDados.Tarefas.Remove(tarefa);
    BancoDados.SalvarDados();
    return true;

`BancoDados.Tarefas.Remove(tarefa)`: remove a tarefa da lista.
`BancoDados.SalvarDados()`: isso é importante para manter a lista atualizada no "banco de dados".
`return true`: indicando que a tarefa foi apagada com sucesso. 


    return false;

- Se o usuário não for admin e não for o dono da tarefa, então não tem permissão;
- Nesse caso, retorna false -> tarefa não apagada.
