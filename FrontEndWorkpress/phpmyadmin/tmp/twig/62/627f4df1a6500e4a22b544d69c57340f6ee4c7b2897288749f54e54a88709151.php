<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* server/privileges/privileges_summary.twig */
class __TwigTemplate_7b0379f7ea6a26d6d630d3860cb5c53e6baa1ea65303de47d78ad6cd6bb24106 extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<form class=\"submenu-item\" action=\"";
        echo PhpMyAdmin\Url::getFromRoute("/server/privileges");
        echo "\" id=\"";
        echo twig_escape_filter($this->env, ($context["form_id"] ?? null), "html", null, true);
        echo "\" method=\"post\">
    ";
        // line 2
        echo PhpMyAdmin\Url::getHiddenInputs();
        echo "
    <input type=\"hidden\" name=\"username\" value=\"";
        // line 3
        echo twig_escape_filter($this->env, ($context["username"] ?? null), "html", null, true);
        echo "\">
    <input type=\"hidden\" name=\"hostname\" value=\"";
        // line 4
        echo twig_escape_filter($this->env, ($context["hostname"] ?? null), "html", null, true);
        echo "\">

    <fieldset>
        <legend data-submenu-label=\"";
        // line 7
        echo twig_escape_filter($this->env, ($context["sub_menu_label"] ?? null), "html", null, true);
        echo "\">
            ";
        // line 8
        echo twig_escape_filter($this->env, ($context["legend"] ?? null), "html", null, true);
        echo "
        </legend>

        <table class=\"table table-light table-striped table-hover w-auto\">
            <thead class=\"thead-light\">
                <tr>
                    <th scope=\"col\">";
        // line 14
        echo twig_escape_filter($this->env, ($context["type_label"] ?? null), "html", null, true);
        echo "</th>
                    <th scope=\"col\">";
        // line 15
        echo _gettext("Privileges");
        echo "</th>
                    <th scope=\"col\">";
        // line 16
        echo _gettext("Grant");
        echo "</th>
                    ";
        // line 17
        if ((($context["type"] ?? null) == "database")) {
            // line 18
            echo "                        <th scope=\"col\">";
            echo _gettext("Table-specific privileges");
            echo "</th>
                    ";
        } elseif ((        // line 19
($context["type"] ?? null) == "table")) {
            // line 20
            echo "                        <th scope=\"col\">";
            echo _gettext("Column-specific privileges");
            echo "</th>
                    ";
        }
        // line 22
        echo "                    <th scope=\"col\" colspan=\"2\">";
        echo _gettext("Action");
        echo "</th>
                </tr>
            </thead>

            <tbody>
                ";
        // line 27
        if ((twig_length_filter($this->env, ($context["privileges"] ?? null)) == 0)) {
            // line 28
            echo "                    ";
            $context["colspan"] = (((($context["type"] ?? null) == "database")) ? (7) : ((((($context["type"] ?? null) == "table")) ? (6) : (5))));
            // line 29
            echo "                    <tr>
                        <td class=\"text-center\" colspan=\"";
            // line 30
            echo twig_escape_filter($this->env, ($context["colspan"] ?? null), "html", null, true);
            echo "\"><em>";
            echo _gettext("None");
            echo "</em></td>
                    </tr>
                ";
        } else {
            // line 33
            echo "                    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["privileges"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["privilege"]) {
                // line 34
                echo "                        <tr>
                            <td>";
                // line 35
                echo twig_escape_filter($this->env, (($__internal_f607aeef2c31a95a7bf963452dff024ffaeb6aafbe4603f9ca3bec57be8633f4 = $context["privilege"]) && is_array($__internal_f607aeef2c31a95a7bf963452dff024ffaeb6aafbe4603f9ca3bec57be8633f4) || $__internal_f607aeef2c31a95a7bf963452dff024ffaeb6aafbe4603f9ca3bec57be8633f4 instanceof ArrayAccess ? ($__internal_f607aeef2c31a95a7bf963452dff024ffaeb6aafbe4603f9ca3bec57be8633f4["name"] ?? null) : null), "html", null, true);
                echo "</td>
                            <td><code>";
                // line 36
                echo (($__internal_62824350bc4502ee19dbc2e99fc6bdd3bd90e7d8dd6e72f42c35efd048542144 = $context["privilege"]) && is_array($__internal_62824350bc4502ee19dbc2e99fc6bdd3bd90e7d8dd6e72f42c35efd048542144) || $__internal_62824350bc4502ee19dbc2e99fc6bdd3bd90e7d8dd6e72f42c35efd048542144 instanceof ArrayAccess ? ($__internal_62824350bc4502ee19dbc2e99fc6bdd3bd90e7d8dd6e72f42c35efd048542144["privileges"] ?? null) : null);
                echo "</code></td>
                            <td>";
                // line 37
                echo twig_escape_filter($this->env, (((($__internal_1cfccaec8dd2e8578ccb026fbe7f2e7e29ac2ed5deb976639c5fc99a6ea8583b = $context["privilege"]) && is_array($__internal_1cfccaec8dd2e8578ccb026fbe7f2e7e29ac2ed5deb976639c5fc99a6ea8583b) || $__internal_1cfccaec8dd2e8578ccb026fbe7f2e7e29ac2ed5deb976639c5fc99a6ea8583b instanceof ArrayAccess ? ($__internal_1cfccaec8dd2e8578ccb026fbe7f2e7e29ac2ed5deb976639c5fc99a6ea8583b["grant"] ?? null) : null)) ? (_gettext("Yes")) : (_gettext("No"))), "html", null, true);
                echo "</td>

                            ";
                // line 39
                if ((($context["type"] ?? null) == "database")) {
                    // line 40
                    echo "                                <td>";
                    echo twig_escape_filter($this->env, (((($__internal_68aa442c1d43d3410ea8f958ba9090f3eaa9a76f8de8fc9be4d6c7389ba28002 = $context["privilege"]) && is_array($__internal_68aa442c1d43d3410ea8f958ba9090f3eaa9a76f8de8fc9be4d6c7389ba28002) || $__internal_68aa442c1d43d3410ea8f958ba9090f3eaa9a76f8de8fc9be4d6c7389ba28002 instanceof ArrayAccess ? ($__internal_68aa442c1d43d3410ea8f958ba9090f3eaa9a76f8de8fc9be4d6c7389ba28002["table_privs"] ?? null) : null)) ? (_gettext("Yes")) : (_gettext("No"))), "html", null, true);
                    echo "</td>
                            ";
                } elseif ((                // line 41
($context["type"] ?? null) == "table")) {
                    // line 42
                    echo "                                <td>";
                    echo twig_escape_filter($this->env, (((($__internal_d7fc55f1a54b629533d60b43063289db62e68921ee7a5f8de562bd9d4a2b7ad4 = $context["privilege"]) && is_array($__internal_d7fc55f1a54b629533d60b43063289db62e68921ee7a5f8de562bd9d4a2b7ad4) || $__internal_d7fc55f1a54b629533d60b43063289db62e68921ee7a5f8de562bd9d4a2b7ad4 instanceof ArrayAccess ? ($__internal_d7fc55f1a54b629533d60b43063289db62e68921ee7a5f8de562bd9d4a2b7ad4["column_privs"] ?? null) : null)) ? (_gettext("Yes")) : (_gettext("No"))), "html", null, true);
                    echo "</td>
                            ";
                }
                // line 44
                echo "
                            <td>";
                // line 45
                echo (($__internal_01476f8db28655ee4ee02ea2d17dd5a92599be76304f08cd8bc0e05aced30666 = $context["privilege"]) && is_array($__internal_01476f8db28655ee4ee02ea2d17dd5a92599be76304f08cd8bc0e05aced30666) || $__internal_01476f8db28655ee4ee02ea2d17dd5a92599be76304f08cd8bc0e05aced30666 instanceof ArrayAccess ? ($__internal_01476f8db28655ee4ee02ea2d17dd5a92599be76304f08cd8bc0e05aced30666["edit_link"] ?? null) : null);
                echo "</td>
                            <td>";
                // line 46
                echo (($__internal_01c35b74bd85735098add188b3f8372ba465b232ab8298cb582c60f493d3c22e = $context["privilege"]) && is_array($__internal_01c35b74bd85735098add188b3f8372ba465b232ab8298cb582c60f493d3c22e) || $__internal_01c35b74bd85735098add188b3f8372ba465b232ab8298cb582c60f493d3c22e instanceof ArrayAccess ? ($__internal_01c35b74bd85735098add188b3f8372ba465b232ab8298cb582c60f493d3c22e["revoke_link"] ?? null) : null);
                echo "</td>
                        </tr>
                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['privilege'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 49
            echo "                ";
        }
        // line 50
        echo "            </tbody>
        </table>

        ";
        // line 53
        if ((($context["type"] ?? null) == "database")) {
            // line 54
            echo "            <label for=\"text_dbname\">";
            echo _gettext("Add privileges on the following database(s):");
            echo "</label>";
            // line 56
            if ( !twig_test_empty(($context["databases"] ?? null))) {
                // line 57
                echo "                <select name=\"pred_dbname[]\" multiple=\"multiple\">
                    ";
                // line 58
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["databases"] ?? null));
                $context['loop'] = [
                  'parent' => $context['_parent'],
                  'index0' => 0,
                  'index'  => 1,
                  'first'  => true,
                ];
                if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                    $length = count($context['_seq']);
                    $context['loop']['revindex0'] = $length - 1;
                    $context['loop']['revindex'] = $length;
                    $context['loop']['length'] = $length;
                    $context['loop']['last'] = 1 === $length;
                }
                foreach ($context['_seq'] as $context["_key"] => $context["database"]) {
                    // line 59
                    echo "                        <option value=\"";
                    echo twig_escape_filter($this->env, PhpMyAdmin\Util::escapeMysqlWildcards((($__internal_63ad1f9a2bf4db4af64b010785e9665558fdcac0e8db8b5b413ed986c62dbb52 = ($context["escaped_databases"] ?? null)) && is_array($__internal_63ad1f9a2bf4db4af64b010785e9665558fdcac0e8db8b5b413ed986c62dbb52) || $__internal_63ad1f9a2bf4db4af64b010785e9665558fdcac0e8db8b5b413ed986c62dbb52 instanceof ArrayAccess ? ($__internal_63ad1f9a2bf4db4af64b010785e9665558fdcac0e8db8b5b413ed986c62dbb52[twig_get_attribute($this->env, $this->source, $context["loop"], "index0", [], "any", false, false, false, 59)] ?? null) : null)), "html", null, true);
                    echo "\">
                            ";
                    // line 60
                    echo twig_escape_filter($this->env, $context["database"], "html", null, true);
                    echo "
                        </option>
                    ";
                    ++$context['loop']['index0'];
                    ++$context['loop']['index'];
                    $context['loop']['first'] = false;
                    if (isset($context['loop']['length'])) {
                        --$context['loop']['revindex0'];
                        --$context['loop']['revindex'];
                        $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                    }
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['database'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 63
                echo "                </select>
            ";
            }
            // line 66
            echo "<input type=\"text\" id=\"text_dbname\" name=\"dbname\">
            ";
            // line 67
            echo \PhpMyAdmin\Html\Generator::showHint(_gettext("Wildcards % and _ should be escaped with a \\ to use them literally."));
            echo "
        ";
        } elseif ((        // line 68
($context["type"] ?? null) == "table")) {
            // line 69
            echo "            <input type=\"hidden\" name=\"dbname\" value=\"";
            echo twig_escape_filter($this->env, ($context["database"] ?? null), "html", null, true);
            echo "\">

            <label for=\"text_tablename\">";
            // line 71
            echo _gettext("Add privileges on the following table:");
            echo "</label>";
            // line 73
            if ( !twig_test_empty(($context["tables"] ?? null))) {
                // line 74
                echo "                <select name=\"pred_tablename\" class=\"autosubmit\">
                    <option value=\"\" selected=\"selected\">";
                // line 75
                echo _gettext("Use text field");
                echo ":</option>
                    ";
                // line 76
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["tables"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["table"]) {
                    // line 77
                    echo "                        <option value=\"";
                    echo twig_escape_filter($this->env, $context["table"], "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $context["table"], "html", null, true);
                    echo "</option>
                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['table'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 79
                echo "                </select>
            ";
            }
            // line 82
            echo "<input type=\"text\" id=\"text_tablename\" name=\"tablename\">
        ";
        } else {
            // line 84
            echo "            <input type=\"hidden\" name=\"dbname\" value=\"";
            echo twig_escape_filter($this->env, ($context["database"] ?? null), "html", null, true);
            echo "\">

            <label for=\"text_routinename\">";
            // line 86
            echo _gettext("Add privileges on the following routine:");
            echo "</label>";
            // line 88
            if ( !twig_test_empty(($context["routines"] ?? null))) {
                // line 89
                echo "                <select name=\"pred_routinename\" class=\"autosubmit\">
                    <option value=\"\" selected=\"selected\">";
                // line 90
                echo _gettext("Use text field");
                echo ":</option>
                    ";
                // line 91
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["routines"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["routine"]) {
                    // line 92
                    echo "                        <option value=\"";
                    echo twig_escape_filter($this->env, $context["routine"], "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $context["routine"], "html", null, true);
                    echo "</option>
                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['routine'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 94
                echo "                </select>
            ";
            }
            // line 97
            echo "<input type=\"text\" id=\"text_routinename\" name=\"routinename\">
        ";
        }
        // line 99
        echo "    </fieldset>

    <fieldset class=\"tblFooters\">
        <input class=\"btn btn-primary\" type=\"submit\" value=\"";
        // line 102
        echo _gettext("Go");
        echo "\">
    </fieldset>
</form>
";
    }

    public function getTemplateName()
    {
        return "server/privileges/privileges_summary.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  333 => 102,  328 => 99,  324 => 97,  320 => 94,  309 => 92,  305 => 91,  301 => 90,  298 => 89,  296 => 88,  293 => 86,  287 => 84,  283 => 82,  279 => 79,  268 => 77,  264 => 76,  260 => 75,  257 => 74,  255 => 73,  252 => 71,  246 => 69,  244 => 68,  240 => 67,  237 => 66,  233 => 63,  216 => 60,  211 => 59,  194 => 58,  191 => 57,  189 => 56,  185 => 54,  183 => 53,  178 => 50,  175 => 49,  166 => 46,  162 => 45,  159 => 44,  153 => 42,  151 => 41,  146 => 40,  144 => 39,  139 => 37,  135 => 36,  131 => 35,  128 => 34,  123 => 33,  115 => 30,  112 => 29,  109 => 28,  107 => 27,  98 => 22,  92 => 20,  90 => 19,  85 => 18,  83 => 17,  79 => 16,  75 => 15,  71 => 14,  62 => 8,  58 => 7,  52 => 4,  48 => 3,  44 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "server/privileges/privileges_summary.twig", "/opt/lampp/phpmyadmin/templates/server/privileges/privileges_summary.twig");
    }
}
