<?php 
class Optimizer {
    public $program;

    public function __construct($program) {
        $this->program = $program;
    }

    public function optimize() {
        $scope = [];
        $i = 0;
        foreach ($this->program->body as $statement) {
            if ($statement instanceof DeclareVariable) {
                array_push($scope, $statement->name);
            } elseif ($statement instanceof DeleteVariable) {
                $scope = array_diff($scope,[$statement->name->value]);
                
            }  elseif ($statement instanceof LoopStatement) {
                $this->program->body[$i] = $this->scoped_loop($statement);
            } elseif ($statement instanceof DeclareFunction) {
                $this->program->body[$i] = $this->scoped_loop($statement);
            } elseif ($statement instanceof IfStatement) {

                $this->program->body[$i] = $this->scoped_loop($statement);
                $ii = 0;
                foreach($statement->tryother as $elif) {
                    $this->program->body[$i]->tryother[$ii]->body = $this->scoped_loop($elif->body);
                }
                if (isset($statement->else)) {
                    $this->program->body[$i]->else = $this->scoped_loop($statement->else, TRUE);
                }

            }
            
            $i += 1;
        }
        foreach (array_reverse($scope) as $var) {
            array_push($this->program->body, new DeleteVariable($var));
        }
        return $this->program;
    }

    private function scoped_loop($statement, $is_else=FALSE) {
        $scope = [];
        $nodes = (!$is_else) ? $statement->body : $statement;
        foreach ($nodes as $statement_node) {
            if ($statement_node instanceof DeclareVariable) {
                array_push($scope, $statement_node->name);
            } elseif ($statement_node instanceof DeleteVariable) {
                $scope = array_diff($scope,[$statement_node->name]);
                
            }  
        }
        foreach (array_reverse($scope) as $var) {
            array_push($statement->body, new DeleteVariable($var));
        }
        return $statement;
    }
}

?>