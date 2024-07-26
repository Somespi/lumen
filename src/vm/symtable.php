<?
class SymbolTable {
    private $symbols = [];

    public function set($name, $value) {
        $this->symbols[$name] = $value;
    }

    public function get($name) {
        if (isset($this->symbols[$name])) {
            return $this->symbols[$name];
        } else {
            throw new Exception("Undefined variable: $name");
        }
    }

    public function has($name) {
        return isset($this->symbols[$name]);
    }
}