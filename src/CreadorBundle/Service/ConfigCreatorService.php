<?php
/**
 * Created by PhpStorm.
 * User: gfonticelli
 * Date: 01/08/17
 * Time: 13:48
 */

namespace CreadorBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class ConfigCreatorService
{

    const DELETE_ME = 'deleteMe';
    const EMPTY_ARRAY = 'emptyArray';

    protected $request;
    protected $kernel;

    /**
     * ConfigCreatorService constructor.
     *
     * @param $request
     */
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function setRequestData(Request $request)
    {
        $this->request = $request;
    }

    public function createDashboardYML()
    {
        $clientName = $this->request->get('clientName');
        $withTest = (!is_null($this->request->get('test')));
        $hierarchy = json_decode($this->request->get('hierarchyStructure'), true);
        $userPositions = $this->request->get('usertypes');
        $filters = $this->request->get('filters');

        $clientConfig = array(
            'users' => $this->createUsersTree(),
            'metrics' => $this->createMetricsTree($hierarchy, $filters),
            'region_metrics' => $this->createRegionMetricsTree(),
            'themes' => $this->createThemeTree(),
            'colors' => $this->createColorsTree(),
            'country_codes' => $this->createCountryCodesTree(),
            'dictionary' => $this->createDictionaryTree($hierarchy, $userPositions, $filters),
        );

        $config[$clientName] = $clientConfig;
        if ($withTest) {
            $config['test'] = $clientConfig;
        }
        $config['_base'] = $this->create_BaseConfig();

        $yaml = Yaml::dump($config, 6);

        $filePath = $this->kernel->getRootDir().'/generated/dashboard.yml';
        file_put_contents($filePath, $yaml);

        $content = file_get_contents($filePath);
        $cleanContent = str_replace(self::DELETE_ME,'',$content);
        $cleanContent = str_replace(self::EMPTY_ARRAY,'[]',$cleanContent);

        file_put_contents($filePath, $cleanContent);
    }

    //********************************************************
    //                        USERS
    //********************************************************

    private function createUsersTree()
    {
        $users = array(
            'types' => $this->getUserTypesConfig(),
        );

        return $users;
    }

    private function getUserTypesConfig()
    {
        return $this->request->get('usertypes');
    }

    //********************************************************
    //                        METRICS
    //********************************************************

    private function createMetricsTree($hierarchy, $filters)
    {
        $metrics = array(
            'filter' => $this->getMetricFilterConfig($filters),
            'commentFilterField' => $this->getMetricCommentFilterFieldConfig(),
            'lowerIsBetter' => $this->getMetricLowerIsBetterConfig(),
            'definitions' => $this->getMetricDefinitionsConfig($hierarchy),
            'hierarchy' => $this->getMetricHierarchyConfig($hierarchy),
            'competitors' => $this->getMetricCompetitorsConfig(),
            'categories' => $this->getMetricCategoriesConfig(),
            'ignore' => $this->getMetricIgnoreConfig()
        );

        return $metrics;
    }

    private function getMetricFilterConfig($filters)
    {
        $filtersConfig = [];

        foreach ($filters as $filter) {
            $filtersConfig[$filter] = 'FILTER_'.$filter;
        }

        return $filtersConfig;
    }

    private function getMetricCommentFilterFieldConfig()
    {
        return 'canal';
    }

    private function getMetricLowerIsBetterConfig()
    {
        return ['RP_TI'];
    }

    private function getMetricDefinitionsConfig($hierarchy)
    {
        $definitions = [];
        foreach ($hierarchy as $key => $item) {
            $definitions[$key] = 'METRIC_'.$key;
            if (is_array($item)) {
                foreach ($item as $key2 => $item2) {
                    $definitions[$key2] = 'METRIC_'.$key2;
                }
            }
        }

        return $definitions;
    }

    private function getMetricHierarchyConfig($array)
    {
        $resultArr = array();
        foreach ($array as $key => $subArr) {
            if (is_array($subArr)) {
                $resultArr[$key] = $this->arrayCombine(array_values($subArr));
            } else {
                $resultArr[$subArr] = self::DELETE_ME;
            }
        }

        return $resultArr;
    }

    private function arrayCombine($array)
    {
        $values = [];
        foreach ($array as $item) {
            $values[] = self::DELETE_ME;
        }

        return array_combine(array_values($array),$values);
    }

    private function getMetricCompetitorsConfig()
    {
        return array(
            'definitions' => self::DELETE_ME,
            'hierarchy' => self::DELETE_ME,
            'countries' => array('Argentina' => self::DELETE_ME),
        );
    }

    private function getMetricCategoriesConfig()
    {
        return array(
            'definitions' => self::EMPTY_ARRAY,
            'hierarchy' => self::EMPTY_ARRAY,
        );
    }

    private function getMetricIgnoreConfig()
    {
        return self::EMPTY_ARRAY;
    }

    //********************************************************
    //                        OTROS
    //********************************************************

    private function createRegionMetricsTree()
    {
        return self::DELETE_ME;
    }

    private function createThemeTree()
    {
        return self::DELETE_ME;
    }

    private function createColorsTree()
    {
        return self::DELETE_ME;
    }

    private function createCountryCodesTree()
    {
        return self::DELETE_ME;
    }

    //********************************************************
    //                        DICCIONARIO
    //********************************************************

    private function createDictionaryTree($hierarchy, $userPositions, $filters)
    {
        $dictionary = array(
            'es' => $this->getDictionaryESConfig($hierarchy, $userPositions, $filters)
        );

        return $dictionary;
    }

    private function getDictionaryESConfig($hierarchy, $userPositions, $filters)
    {
        return array_merge(
            $this->getHierarchyTransConfig($hierarchy),
            $this->getBaseDicctionaryConfig(),
            $this->getUserPositionsTransConfig($userPositions),
            $this->getFiltersTransConfig($filters)
        );
    }

    private function getUserPositionsTransConfig($userPositions)
    {
        $userPositionsTrans = [];
        foreach ($userPositions as $userPosition) {
            $userPositionsTrans['USER_POSITION_'.$userPosition] =  $this->request->get('trans_'.$userPosition);
        }
        return $userPositionsTrans;
    }

    private function getFiltersTransConfig($filters)
    {
        $filterTrans = [];
        foreach ($filters as $filter) {
            $filterTrans['FILTER_'.$filter] =  $this->request->get('trans_'.$filter);
        }
        return $filterTrans;
    }

    private function getHierarchyTransConfig($hierarchy)
    {
        $hierarchyTransArray = [];

        foreach ($hierarchy as $key => $item) {
            $hierarchyTransArray['METRIC_'.$key] = $this->request->get('trans_'.$key);
            if (is_array($item)) {
                foreach ($item as $key2 => $item2) {
                    $hierarchyTransArray['METRIC_'.$key2] = $this->request->get('trans_'.$key2);
                }
            }
        }

        return $hierarchyTransArray;
    }

    private function getBaseDicctionaryConfig()
    {
        $dictionary = array(
            'APP_NAME'                  => 'Centro Atención de Salud',
            'FILTER_TITLE'              => 'Canal',
            'FILTER_CT'                 => 'Telefónico',
            'FILTER_CW'                 => 'Web',
            'PENETRATION'               => 'Penetración',
            'SATISFACTION'              => 'Satisfacción',
            'CATEGORY_DAYSOFWEEK'       => 'Fin de semana',
            'CATEGORY_WEEKEND'          => 'Fin de semana',
            'CATEGORY_WEEKDAYS'         => 'Días de semana',
            'CATEGORY_OWNER'            => 'Propietario',
            'CATEGORY_OWN'              => 'Propio',
            'CATEGORY_FRANCHISE'        => 'Franquicias',
            'CATEGORY_REGION'           => 'Region',
            'CATEGORY_REGION_CABA'      => 'CABA',
            'CATEGORY_REGION_GBA'       => 'GBA',
            'CATEGORY_REGION_INTERIOR'  => 'Interior',
            'SATISFACTION_5' => 'Muy Satisfecho',
            'SATISFACTION_4' => 'Satisfecho',
            'SATISFACTION_3' => 'Ni satisfecho ni insatisfecho',
            'SATISFACTION_2' => 'Insatisfecho',
            'SATISFACTION_1' => 'Muy Insatisfecho',
            'SATISFACTION_0' => 'No Evalua',
            'NPS_3' => 'Promotores',
            'NPS_2' => 'Neutros',
            'NPS_1' => 'Detractores',
            'GENDER_FEMALE' => 'Femenino',
            'GENDER_MALE' => 'Masculino',
            'PROPIO' => 'Propio',
            'FRANQUICIADO' => 'Franquiciado',
            'CABA' => 'CABA',
            'GBA' => 'GBA',
            'Interior' => 'Interior',
            'IVL_SATISFACTION_5' => "Seguramente volveré",
            'IVL_SATISFACTION_4' => "Probablemente volveré",
            'IVL_SATISFACTION_3' => "No sé si volveré",
            'IVL_SATISFACTION_2' => "Probablemente no volveré",
            'IVL_SATISFACTION_1' => "Seguramente no volveré",
            'IVL_SATISFACTION_0' => "No Evalua",

            'WELCOME' => 'Bienvenido',
            'DOWNLOAD_PDF' => 'Descargar PDF',
            'ITEM_DOWNLOAD_PDF' => '<b>Descargar PDF</b> de la<br/>vista actual',
            'DOWNLOAD_HIERARCHY_PDF' => 'Reportes Est. Op.',
            'ITEM_DOWNLOAD_HIERARCHY_PDF' => '<b>Exportar PDF</b> de toda la<br/>Estructura de Operaciones',
            'EXPORT_XLS' => 'Descargar Excel',
            'MENU_EXPORT_XLS' => 'Exportar Tabla',
            'MENU_EXPORT_TITLE' => 'Tipo de archivo',
            'ITEM_EXPORT_CSV' => '<b>CSV</b> <i>(Descarga Rápida)</i>',
            'ITEM_EXPORT_XLS' => '<b>Excel</b>',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_TITLE' => 'Reportes de la estructura de operaciones',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_EXPLAIN' => 'Se programará la creación de reportes en PDF de todos los usuarios debajo en la estructura de operaciones de <b><%= username %></b>. Al finalizar la creación, se le enviará un email con el link de descarga.',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_QUESTION' => '¿Está seguro que desea continuar?',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_BUTTON' => 'Crear',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_CANCEL' => 'Cancelar',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_OK' => 'Se agendó la tarea de exportación de reportes para toda la jerarquía. Al finalizar, se le enviará un email donde podrá descargar todos los reportes.',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_ALREADY_EXIST' => 'Ya tiene agendada una tarea de exportación',
            'MESSAGE_EXPORT_CASES' => 'Se ha iniciado la descarga de los datos de casos, este proceso puede tardar varios minutos, espere por favor.',
            'MENU_SUMMARY' => 'Resumen',
            'MENU_REPORT' => 'Reporte',
            'MENU_DIMENSION' => 'Indicadores',
            'MENU_HISTORY' => 'Histórico',
            'MENU_CASES' => 'Casos',
            'MENU_COMMENT' => 'Comentarios',
            'MENU_OPERATIONS' => 'Estruct. de Op.',
            'MENU_COMPETENCE' => 'Competencia',
            'MENU_MANUAL' => 'Manual',
            'MENU_ANALYSIS' => 'Análisis',
            'MENU_FAQS' => 'FAQs',
            'MENU_HELP' => 'Ayuda',
            'USER_CHANGE_EMAIL' => 'Cambiar E-mail',
            'USER_CHANGE_PASSWORD' => 'Cambiar Contraseña',
            'USER_EXIT' => 'Salir',
            'REPORT_CHOOSE_COUNTRY' => 'Seleccione país',
            'REPORT_HOW_IMPROVE' => '¿Qué podemos mejorar?',
            'REPORT_HOW_DOING' => '¿Cómo nos está yendo?',
            'REPORT_WHAT_SAYING' => '¿Qué nos están diciendo?',
            'REPORT_TITLE_PERFORMANCE_TABLE' => 'Desempeño',
            'REPORT_TITLE_WORD_CLOUD' => 'Nube Términos',
            'REPORT_TITLE_EVOLUTION' => 'Evolución',
            'REPORT_TITLE_YEAR_TO_YEAR' => 'Comparable',
            'REPORT_SUBTITLE_YEAR_TO_YEAR' => '<%= wave_act %> vs <%= wave_old %>',
            'REPORT_TITLE_CASES' => 'Casos',
            'REPORT_TITLE_LAST_COMMENTS' => 'Últimos Comentarios',
            'REPORT_TITLE_TOP_BOTTOM_PERFORMANCE_TABLE' => 'Desempeño centro de at.',
            'REPORT_TITLE_GENERAL_SATISFACTION' => 'Satisfacción General',
            'REPORT_TITLE_RANKING' => 'Ranking',
            'REPORT_SUBTITLE_RANKING' => 'Top Box',
            'REPORT_TITLE_DAYSOFWEEK' => 'Momento de la semana',
            'REPORT_TITLE_ZONE' => 'Zona',
            'REPORT_TITLE_AGENCY' => 'Agencia',
            'REPORT_VS_TM' => 'Vs. Anterior',
            'REPORT_PROM_COUNTRY_TM' => 'País',
            'REPORT_MCD_TOP' => 'GR TOP',
            'WIDGET_METRIC_SUMMARY_LEVEL' => 'Nivel',
            'WIDGET_METRIC_SUMMARY_NATIONAL' => 'Nacional',
            'WIDGET_METRIC_SUMMARY_ATTRIBUTES' => 'Atributos',
            'WIDGET_LAST_COMMENT_MESSAGE' => 'No hay comentarios para los criterios de búsqueda definidos',
            'WIDGET_RANKING_POSITION' => 'Posición',
            'WIDGET_CASES_SUMMARY_STORES' => 'Centros de atención',
            'WIDGET_CASES_SUMMARY_INSUFFICENT_BASES' => 'Base Insuficiente',
            'WIDGET_CASES_SUMMARY_INSUFFICENTS' => 'Insuficientes',
            'WIDGET_TOP_BOTTOM_PERFORMANCE_TABLE_TOP' => 'TOP',
            'WIDGET_TOP_BOTTOM_PERFORMANCE_TABLE_BOTTOM' => 'BOTTOM',
            'WIDGET_SEARCH_SEARCH' => 'Buscar',
            'WIDGET_SEARCH_PLACEHOLDER' => 'Buscar Centro de atención, Gerente, Zonal, etc...',
            'WIDGET_BREADCRUMBS_SELECT' => 'Seleccionar',
            'WIDGET_EVOLUTION_GRAPH_INSUFFICENT_CASES' => 'Cantidad de casos insuficientes',
            'WIDGET_EVOLUTION_GRAPH_CASES' => 'Casos',
            'WIDGET_METRIC_RANKING_POSITION' => 'Posición',
            'WIDGET_METRIC_RANKING_NAME' => 'Nombre',
            'WIDGET_METRIC_RANKING_CASES' => 'Casos',
            'WIDGET_CATEGORIES_BAR_INSUFFICENT_CASES' => 'Cantidad de casos insuficientes',
            'WIDGET_CATEGORIES_BAR_CASES' => 'Casos',
            'WIDGET_CATEGORIES_BAR_VALUE' => 'Valor',
            'WIDGET_CHILDREN_PERFORMANCE_TABLE_ABOVE_COUNTRY' => 'Sobre País',
            'WIDGET_CHILDREN_PERFORMANCE_TABLE_MATCH_COUNTRY' => 'Igual País',
            'WIDGET_CHILDREN_PERFORMANCE_TABLE_BELOW_COUNTRY' => 'Debajo País',
            'WIDGET_COMMENT_TABLE_TITLE' => 'Comentarios',
            'WIDGET_COMMENT_TABLE_COLUMN_STORE' => 'Sucursal',
            'WIDGET_COMMENT_TABLE_COLUMN_DATE' => 'Fecha',
            'WIDGET_COMMENT_TABLE_COLUMN_HOUR' => 'Hora',
            'WIDGET_COMMENT_TABLE_COLUMN_DATETIME' => 'Fecha y Hora',
            'WIDGET_COMMENT_TABLE_COLUMN_GENDER' => 'Genero',
            'WIDGET_COMMENT_TABLE_COLUMN_AGE' => 'Edad',
            'WIDGET_COMMENT_TABLE_SEGMENT' => 'Segmento',
            'WIDGET_COMMENT_TABLE_COLUMN_GENERAL_SAT' => 'S. General',
            'WIDGET_COMMENT_TABLE_COLUMN_COMMENT' => 'Comentario',
            'WIDGET_COMMENT_TABLE_NO_INFO_MESSAGE' => 'No hay comentarios correspondientes a la dimensión seleccionada',
            'WIDGET_COMPETITOR_GRAPH_PENETRATION' => 'Penetración',
            'WIDGET_COMPETITOR_GRAPH_SATISFACTION' => 'Satisfacción',
            'WIDGET_COMPETITOR_GRAPH_CASES' => 'Casos',
            'WIDGET_COMPETITOR_GRAPH_INSUFFICENT_CASES' => 'Cantidad de casos insuficientes',
            'WIDGET_CROSSINGS_GRAPH_PENETRATION' => 'Penetración',
            'WIDGET_CROSSINGS_GRAPH_SATISFACTION' => 'Satisfacción',
            'WIDGET_CROSSINGS_GRAPH_USER_SATISFACTION' => 'Satisfacción de <%= user %>',
            'WIDGET_METRIC_GRAPH_PENETRATION' => 'Penetración',
            'WIDGET_METRIC_GRAPH_SATISFACTION' => 'Satisfacción',
            'WIDGET_MANAGER_DYNAMIC_COMMENT_FILTER_TITLE' => 'Filtro',
            'WIDGET_MANAGER_DYNAMIC_COMMENT_FILTER_SEARCH' => 'Buscar',
            'WIDGET_MANAGER_DYNAMIC_COMMENT_FILTER_EXPORT' => 'Descargar XLS',
            'WIDGET_MANAGER_DYNAMIC_COMMENT_FILTER_RESET' => 'Reset',
            'WIDGET_INDICATOR_FILTER_INDICATOR' => 'Indicador',
            'WIDGET_INDICATOR_FILTER_SATISFACTION' => 'Satisfacción',
            'WIDGET_COMMENT_FILTER_ALL' => 'Todos',
            'WIDGET_LIST_COMMENT_AGE' => '<%= age %> Años',
            'WIDGET_HISTORY_CASES_TABLE_INSUFFICENT_CASES' => 'Casos insuficientes',
            'WIDGET_WORD_CLOUD_NO_COMMENTS' => 'No hay comentarios disponibles',
            'WIDGET_CROSSINGS_GRAPH_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_CATEGORIES_BAR_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_COMPETITORS_CATEGORIES_BAR_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_RANKING_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_METRIC_SUMMARY_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_CATEGORIES_SUMMARY_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_TOP_BOTTOM_PERFORMANCE_TABLE_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_METRIC_SAT_GRAL' => 'Sat. Gral',
            'WIDGET_METRIC_CASOS' => 'Casos',
            'WIDGET_PAGE_INDICATOR_CASES' => '<%= cases %> Casos',
            'WIDGET_VIDEO_NOT_FOUND' => 'El manual no está disponible en su idioma todavía',
            'WIDGET_VIDEO_CONTINUE' => 'Continuar',
            'WIDGET_FILTER_DATE_FROM' => 'Desde',
            'WIDGET_FILTER_DATE_TO' => 'Hasta',
            'WIDGET_FILTER_AGE_FROM' => 'Desde',
            'WIDGET_FILTER_AGE_TO' => 'Hasta',
            'WIDGET_CHANGE_LANGUAGE' => 'Idioma',
            'WIDGET_GROUP_CREATE' => 'Crear',
            'WIDGET_GROUP_EDIT' => 'Editar',
            'WIDGET_GROUP_DELETE' => 'Borrar',
            'WIDGET_GROUP_NAME' => 'Nombre',
            'WIDGET_GROUP_DESCRIPTION' => 'Descripción',
            'WIDGET_GROUP_COUNTRY' => 'País',
            'WIDGET_GROUP_DELETE_MESSAGE' => 'El grupo <%= groupName %> se borró exitosamente.',
            'WIDGET_GROUP_FILTER' => 'Escriba el nombre de las sucursal que desea buscar',
            'WIDGET_GROUP_AVAILABLE_STORES' => 'Sucursales disponibles',
            'WIDGET_GROUP_MEMBERS' => 'Miembros',
            'WIDGET_GROUP_CANCEL' => 'Cancelar',
            'WIDGET_GROUP_SAVE' => 'Guardar',
            'WIDGET_GROUP_ERROR_NO_NAME' => 'El grupo no puede no tener nombre.',
            'WIDGET_GROUP_ERROR_NO_MEMBERS' => 'La lista de miembros no puede estar vacia.',
            'WIDGET_GROUP_ERROR_UNEXPECTED' => 'Ha ocurrido un error inesperado.',
            'WIDGET_GROUP_SAVE_SUCCESSFUL' => 'Grupo guardado!',
            'WIDGET_GROUP_DELETE_SUCCESSFUL' => 'Grupo eliminado!',
            'WIDGET_GROUP_ADMIN' => 'Administrar Grupos',
            'WIDGET_GROUP_TITLE_PAGE' => 'Grupos',
            'WIDGET_GROUP_WELCOME_WITH_GROUPS' =>
                '<h4><u>Bienvenido a la sección Grupos</u></h4>
                <ul>
                <li>Seleccione el grupo que desea visualizar en el menú de arriba.</li>
                <li>Para crear, borrar y editar grupos, haga click en el botón "Administrar grupos".</li>
                </ul>',
            'WIDGET_GROUP_WELCOME_WITHOUT_GROUPS' =>
                '<h4><u>Bienvenido a la sección Grupos</u></h4>
                <p>Usted no tiene ningún grupo creado.<br/>Para crear uno, diríjase a la sección "Administrar grupos".</p>',
            'WIDGET_GROUP_EDITING_GROUP' => 'Editando grupo <%= groupName %>',
            'WIDGET_GROUP_CREATING_GROUP' => 'Creando nuevo grupo',
            'WIDGET_GROUP_GO_BACK' => 'Volver',
            'WIDGET_GROUP_SAVE_WARNING' => 'Se estan calculando las metricas del grupo, esta operación puede tardar varios minutos, espere por favor',
            'WIDGET_GROUP_DELETE_WARNING_QUESTION' => 'Está seguro?',
            'WIDGET_GROUP_DELETE_WARNING_MESSAGE' => 'No podrá recuperar el grupo una vez eliminado',
            'WIDGET_GROUP_DELETE_WARNING_BUTTON' => 'Si, bórralo!',
            'WIDGET_MANUAL_NOT_FOUND' => 'No hay manual disponible para su idioma',
            'WIDGET_FAQS_NOT_FOUND' => 'No hay preguntas frecuentes disponibles para su idioma',
            'DIMENSION_DASHBOARD_TAB' => 'Dashboard',
            'DIMENSION_DASHBOARD_TITLE_EVOLUTION' => 'Evolución',
            'DIMENSION_DASHBOARD_TITLE_ATTRIBUTES' => 'Atributos',
            'DIMENSION_DASHBOARD_TITLE_PERFORMANCE' => 'Performance',
            'DIMENSION_DASHBOARD_TITLE_CROSSINGS' => 'Cruces',
            'DIMENSION_DASHBOARD_TITLE_LOW_LEVEL' => 'Nivel Inferior',
            'DIMENSION_DASHBOARD_TITLE_STORE_RANKING' => 'Ranking centro de at.',
            'DIMENSION_DASHBOARD_TITLE_NPS_EXPLANATION' => 'Definición de NPS',
            'DIMENSION_DASHBOARD_VS_TM' => 'Vs. Anterior',
            'DIMENSION_DASHBOARD_PROM_COUNTRY_TM' => 'País',
            'DIMENSION_DASHBOARD_MCD_TOP' => 'GR TOP',
            'DIMENSION_RANKING_TAB' => 'Ranking',
            'DIMENSION_RANKING_TITLE_RANKING' => 'Ranking',
            'DIMENSION_RANKING_TITLE_ATTRIBUTES' => 'Atributos',
            'DIMENSION_COMMENTS_TAB' => 'Comentarios',
            'DIMENSION_DASHBOARD_NPS_EXPLANATION' =>
                '<p>En una escala de recomendación de la sucursal los clientes son clasificados en tres grupos:</p>
                <ul>
                <li>Los que responden 9 ó 10 puntos: promotores</li>
                <li>Los que asignan 7 u 8 puntos: pasivos</li>
                <li>Los que otorgan 6 puntos o menos: detractores</li>
                </ul>
                <p>Para obtener el NPS (Net Promoter Score) se restan los Detractores a los Promotores y se obtiene un porcentaje.<br>
                El índice NPS puede tener como mínimo -100 (todos los clientes son detractores) o +100 como máximo (todos son promotores).<br>
                Un NPS superior a 0 es bueno (al menos el saldo de promotores es positivo) y un NPS de 50 se considera excelente.</p>',
            'HISTORY_TAB_DIMENSION' => 'Dimensiones',
            'HISTORY_TAB_OWNER' => 'Propios - Franquiciados',
            'HISTORY_TAB_REGION' => 'Región',
            'HISTORY_TITLE_HISTORIC' => 'Histórico',
            'HISTORY_TITLE_DIMENSION' => 'Dimensiones',
            'HISTORY_TITLE_REGIONS' => 'Regiones',
            'HISTORY_TITLE_OWNER' => 'Propietario',
            'HISTORY_TITLE_HOURS' => 'Horarios',
            'HISTORY_TITLE_DAYS' => 'Días',
            'HISTORY_TITLE_COMPETENCE' => 'Competencia',
            'CASES_TITLE_CASE_HISTORY' => 'Historial Casos',
            'OPERATIONS_TITLE_HIERARCHY' => 'Estructura Operaciones',
            'COMPETENCE_TITLE_EVOLUTION' => 'Evolución',
            'COMPETENCE_TITLE_METRICS' => 'Competidores',
            'COMPETENCE_TITLE_COMPETENCE' => 'Competencia',
            'COMPETENCE_TITLE_CROSSINGS' => 'Satisfacción / Penetración de competencia',
            'COMPETENCE_TITLE_PENETRATION' => 'Penetración',
            'COMPETENCE_TITLE_SATISFACTION' => 'Satisfacción',
            'COMPETENCE_TITLE_COMPETITORS' => 'Competidores',
            'COMMENT_SEARCH' => 'Buscar',
            'COMMENT_SEARCH_PLACEHOLDER' => 'Buscar comentario',
            'COMMENT_DATE' => 'Fecha',
            'COMMENT_GENDER' => 'Género',
            'COMMENT_AGE' => 'Edad',
            'COMMENT_SEGMENTS' => 'Segmentos',
            'COMMENT_DAYS' => 'Días',
            'COMMENT_HOURS' => 'Horarios',
            'COMMENT_CATEGORY' => 'Categoria',
            'COMMENT_TITLE_THEME_CLOUD' => 'Unidades temáticas',
            'COMMENT_TITLE_WORD_CLOUD' => 'Nube términos',
            'COMMENT_TITLE_LIST_COMMENTS' => 'Comentarios',
            'REGION_NAME_LATAM' => 'Latinoamérica',
            'NO_DATA' => 'No hay datos disponibles',
            'COMMENT_SEARCH_RESULTS' => 'Se encontraron <%= results %> resultados'
        );

        return $dictionary;
    }

    private function create_BaseConfig()
    {
        $_base = array(
            'dictionary' => $this->get_BaseDictionaryConfig()
        );

        return $_base;
    }

    private function get_BaseDictionaryConfig()
    {
        $dictionary = array(
            'es' => $this->get_BaseDictionaryESConfig()
        );

        return $dictionary;
    }

    public function get_BaseDictionaryESConfig()
    {
        return array(
            'WELCOME' => 'Bienvenido',
            'DOWNLOAD_PDF' => 'Descargar PDF',
            'ITEM_DOWNLOAD_PDF' => '<b>Descargar PDF</b> de la<br/>vista actual',
            'DOWNLOAD_HIERARCHY_PDF' => 'Reportes Est. Op.',
            'ITEM_DOWNLOAD_HIERARCHY_PDF' => '<b>Exportar PDF</b> de toda la<br/>Estructura de Operaciones',
            'EXPORT_XLS' => 'Descargar Excel',
            'MENU_EXPORT_XLS' => 'Exportar Tabla',
            'MENU_EXPORT_TITLE' => 'Tipo de archivo',
            'ITEM_EXPORT_CSV' => '<b>CSV</b> <i>(Descarga Rápida)</i>',
            'ITEM_EXPORT_XLS' => '<b>Excel</b>',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_TITLE' => 'Reportes de la estructura de operaciones',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_EXPLAIN' => 'Se programará la creación de reportes en PDF de todos los usuarios debajo en la estructura de operaciones de <b><%= username %></b>. Al finalizar la creación, se le enviará un email con el link de descarga.',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_QUESTION' => '¿Está seguro que desea continuar?',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_BUTTON' => 'Crear',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_CANCEL' => 'Cancelar',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_OK' => 'Se agendó la tarea de exportación de reportes para toda la jerarquía. Al finalizar, se le enviará un email donde podrá descargar todos los reportes.',
            'MESSAGE_EXPORT_REPORT_HIERARCHY_ALREADY_EXIST' => 'Ya tiene agendada una tarea de exportación',
            'MESSAGE_EXPORT_CASES' => 'Se ha iniciado la descarga de los datos de casos, este proceso puede tardar varios minutos, espere por favor.',
            'MENU_SUMMARY' => 'Resumen',
            'MENU_REPORT' => 'Reporte',
            'MENU_DIMENSION' => 'Indicadores',
            'MENU_HISTORY' => 'Histórico',
            'MENU_CASES' => 'Casos',
            'MENU_COMMENT' => 'Comentarios',
            'MENU_OPERATIONS' => 'Estruct. de Op.',
            'MENU_COMPETENCE' => 'Competencia',
            'MENU_MANUAL' => 'Manual',
            'MENU_ANALYSIS' => 'Análisis',
            'MENU_FAQS' => 'FAQs',
            'MENU_HELP' => 'Ayuda',
            'USER_CHANGE_EMAIL' => 'Cambiar E-mail',
            'USER_CHANGE_PASSWORD' => 'Cambiar Contraseña',
            'USER_EXIT' => 'Salir',
            'REPORT_CHOOSE_COUNTRY' => 'Seleccione país',
            'REPORT_HOW_IMPROVE' => '¿Qué podemos mejorar?',
            'REPORT_HOW_DOING' => '¿Cómo nos está yendo?',
            'REPORT_WHAT_SAYING' => '¿Qué nos están diciendo?',
            'REPORT_TITLE_PERFORMANCE_TABLE' => 'Desempeño',
            'REPORT_TITLE_WORD_CLOUD' => 'Nube Términos',
            'REPORT_TITLE_EVOLUTION' => 'Evolución',
            'REPORT_TITLE_YEAR_TO_YEAR' => 'Comparable',
            'REPORT_SUBTITLE_YEAR_TO_YEAR' => '<%= wave_act %> vs <%= wave_old %>',
            'REPORT_TITLE_CASES' => 'Casos',
            'REPORT_TITLE_LAST_COMMENTS' => 'Últimos Comentarios',
            'REPORT_TITLE_TOP_BOTTOM_PERFORMANCE_TABLE' => 'Desempeño centro de at.',
            'REPORT_TITLE_GENERAL_SATISFACTION' => 'Satisfacción General',
            'REPORT_TITLE_RANKING' => 'Ranking',
            'REPORT_SUBTITLE_RANKING' => 'Top Box',
            'REPORT_TITLE_DAYSOFWEEK' => 'Momento de la semana',
            'REPORT_TITLE_ZONE' => 'Zona',
            'REPORT_TITLE_AGENCY' => 'Agencia',
            'REPORT_VS_TM' => 'Vs. Anterior',
            'REPORT_PROM_COUNTRY_TM' => 'País',
            'REPORT_MCD_TOP' => 'GR TOP',
            'WIDGET_METRIC_SUMMARY_LEVEL' => 'Nivel',
            'WIDGET_METRIC_SUMMARY_NATIONAL' => 'Nacional',
            'WIDGET_METRIC_SUMMARY_ATTRIBUTES' => 'Atributos',
            'WIDGET_LAST_COMMENT_MESSAGE' => 'No hay comentarios para los criterios de búsqueda definidos',
            'WIDGET_RANKING_POSITION' => 'Posición',
            'WIDGET_CASES_SUMMARY_STORES' => 'Centros de atención',
            'WIDGET_CASES_SUMMARY_INSUFFICENT_BASES' => 'Base Insuficiente',
            'WIDGET_CASES_SUMMARY_INSUFFICENTS' => 'Insuficientes',
            'WIDGET_TOP_BOTTOM_PERFORMANCE_TABLE_TOP' => 'TOP',
            'WIDGET_TOP_BOTTOM_PERFORMANCE_TABLE_BOTTOM' => 'BOTTOM',
            'WIDGET_SEARCH_SEARCH' => 'Buscar',
            'WIDGET_SEARCH_PLACEHOLDER' => 'Buscar Centro de atención, Gerente, Zonal, etc...',
            'WIDGET_BREADCRUMBS_SELECT' => 'Seleccionar',
            'WIDGET_EVOLUTION_GRAPH_INSUFFICENT_CASES' => 'Cantidad de casos insuficientes',
            'WIDGET_EVOLUTION_GRAPH_CASES' => 'Casos',
            'WIDGET_METRIC_RANKING_POSITION' => 'Posición',
            'WIDGET_METRIC_RANKING_NAME' => 'Nombre',
            'WIDGET_METRIC_RANKING_CASES' => 'Casos',
            'WIDGET_CATEGORIES_BAR_INSUFFICENT_CASES' => 'Cantidad de casos insuficientes',
            'WIDGET_CATEGORIES_BAR_CASES' => 'Casos',
            'WIDGET_CATEGORIES_BAR_VALUE' => 'Valor',
            'WIDGET_CHILDREN_PERFORMANCE_TABLE_ABOVE_COUNTRY' => 'Sobre País',
            'WIDGET_CHILDREN_PERFORMANCE_TABLE_MATCH_COUNTRY' => 'Igual País',
            'WIDGET_CHILDREN_PERFORMANCE_TABLE_BELOW_COUNTRY' => 'Debajo País',
            'WIDGET_COMMENT_TABLE_TITLE' => 'Comentarios',
            'WIDGET_COMMENT_TABLE_COLUMN_STORE' => 'Sucursal',
            'WIDGET_COMMENT_TABLE_COLUMN_DATE' => 'Fecha',
            'WIDGET_COMMENT_TABLE_COLUMN_HOUR' => 'Hora',
            'WIDGET_COMMENT_TABLE_COLUMN_DATETIME' => 'Fecha y Hora',
            'WIDGET_COMMENT_TABLE_COLUMN_GENDER' => 'Genero',
            'WIDGET_COMMENT_TABLE_COLUMN_AGE' => 'Edad',
            'WIDGET_COMMENT_TABLE_SEGMENT' => 'Segmento',
            'WIDGET_COMMENT_TABLE_COLUMN_GENERAL_SAT' => 'S. General',
            'WIDGET_COMMENT_TABLE_COLUMN_COMMENT' => 'Comentario',
            'WIDGET_COMMENT_TABLE_NO_INFO_MESSAGE' => 'No hay comentarios correspondientes a la dimensión seleccionada',
            'WIDGET_COMPETITOR_GRAPH_PENETRATION' => 'Penetración',
            'WIDGET_COMPETITOR_GRAPH_SATISFACTION' => 'Satisfacción',
            'WIDGET_COMPETITOR_GRAPH_CASES' => 'Casos',
            'WIDGET_COMPETITOR_GRAPH_INSUFFICENT_CASES' => 'Cantidad de casos insuficientes',
            'WIDGET_CROSSINGS_GRAPH_PENETRATION' => 'Penetración',
            'WIDGET_CROSSINGS_GRAPH_SATISFACTION' => 'Satisfacción',
            'WIDGET_CROSSINGS_GRAPH_USER_SATISFACTION' => 'Satisfacción de <%= user %>',
            'WIDGET_METRIC_GRAPH_PENETRATION' => 'Penetración',
            'WIDGET_METRIC_GRAPH_SATISFACTION' => 'Satisfacción',
            'WIDGET_MANAGER_DYNAMIC_COMMENT_FILTER_TITLE' => 'Filtro',
            'WIDGET_MANAGER_DYNAMIC_COMMENT_FILTER_SEARCH' => 'Buscar',
            'WIDGET_MANAGER_DYNAMIC_COMMENT_FILTER_EXPORT' => 'Descargar XLS',
            'WIDGET_MANAGER_DYNAMIC_COMMENT_FILTER_RESET' => 'Reset',
            'WIDGET_INDICATOR_FILTER_INDICATOR' => 'Indicador',
            'WIDGET_INDICATOR_FILTER_SATISFACTION' => 'Satisfacción',
            'WIDGET_COMMENT_FILTER_ALL' => 'Todos',
            'WIDGET_LIST_COMMENT_AGE' => '<%= age %> Años',
            'WIDGET_HISTORY_CASES_TABLE_INSUFFICENT_CASES' => 'Casos insuficientes',
            'WIDGET_WORD_CLOUD_NO_COMMENTS' => 'No hay comentarios disponibles',
            'WIDGET_CROSSINGS_GRAPH_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_CATEGORIES_BAR_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_COMPETITORS_CATEGORIES_BAR_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_RANKING_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_METRIC_SUMMARY_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_CATEGORIES_SUMMARY_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_TOP_BOTTOM_PERFORMANCE_TABLE_NO_DATA' => 'No hay datos disponibles',
            'WIDGET_METRIC_SAT_GRAL' => 'Sat. Gral',
            'WIDGET_METRIC_CASOS' => 'Casos',
            'WIDGET_PAGE_INDICATOR_CASES' => '<%= cases %> Casos',
            'WIDGET_VIDEO_NOT_FOUND' => 'El manual no está disponible en su idioma todavía',
            'WIDGET_VIDEO_CONTINUE' => 'Continuar',
            'WIDGET_FILTER_DATE_FROM' => 'Desde',
            'WIDGET_FILTER_DATE_TO' => 'Hasta',
            'WIDGET_FILTER_AGE_FROM' => 'Desde',
            'WIDGET_FILTER_AGE_TO' => 'Hasta',
            'WIDGET_CHANGE_LANGUAGE' => 'Idioma',
            'WIDGET_GROUP_CREATE' => 'Crear',
            'WIDGET_GROUP_EDIT' => 'Editar',
            'WIDGET_GROUP_DELETE' => 'Borrar',
            'WIDGET_GROUP_NAME' => 'Nombre',
            'WIDGET_GROUP_DESCRIPTION' => 'Descripción',
            'WIDGET_GROUP_COUNTRY' => 'País',
            'WIDGET_GROUP_DELETE_MESSAGE' => 'El grupo <%= groupName %> se borró exitosamente.',
            'WIDGET_GROUP_FILTER' => 'Escriba el nombre de las sucursal que desea buscar',
            'WIDGET_GROUP_AVAILABLE_STORES' => 'Sucursales disponibles',
            'WIDGET_GROUP_MEMBERS' => 'Miembros',
            'WIDGET_GROUP_CANCEL' => 'Cancelar',
            'WIDGET_GROUP_SAVE' => 'Guardar',
            'WIDGET_GROUP_ERROR_NO_NAME' => 'El grupo no puede no tener nombre.',
            'WIDGET_GROUP_ERROR_NO_MEMBERS' => 'La lista de miembros no puede estar vacia.',
            'WIDGET_GROUP_ERROR_UNEXPECTED' => 'Ha ocurrido un error inesperado.',
            'WIDGET_GROUP_SAVE_SUCCESSFUL' => 'Grupo guardado!',
            'WIDGET_GROUP_DELETE_SUCCESSFUL' => 'Grupo eliminado!',
            'WIDGET_GROUP_ADMIN' => 'Administrar Grupos',
            'WIDGET_GROUP_TITLE_PAGE' => 'Grupos',
            'WIDGET_GROUP_WELCOME_WITH_GROUPS' =>
                '<h4><u>Bienvenido a la sección Grupos</u></h4>
                <ul>
                <li>Seleccione el grupo que desea visualizar en el menú de arriba.</li>
                <li>Para crear, borrar y editar grupos, haga click en el botón "Administrar grupos".</li>
                </ul>',
            'WIDGET_GROUP_WELCOME_WITHOUT_GROUPS' =>
                '<h4><u>Bienvenido a la sección Grupos</u></h4>
                <p>Usted no tiene ningún grupo creado.<br/>Para crear uno, diríjase a la sección "Administrar grupos".</p>',
            'WIDGET_GROUP_EDITING_GROUP' => 'Editando grupo <%= groupName %>',
            'WIDGET_GROUP_CREATING_GROUP' => 'Creando nuevo grupo',
            'WIDGET_GROUP_GO_BACK' => 'Volver',
            'WIDGET_GROUP_SAVE_WARNING' => 'Se estan calculando las metricas del grupo, esta operación puede tardar varios minutos, espere por favor',
            'WIDGET_GROUP_DELETE_WARNING_QUESTION' => 'Está seguro?',
            'WIDGET_GROUP_DELETE_WARNING_MESSAGE' => 'No podrá recuperar el grupo una vez eliminado',
            'WIDGET_GROUP_DELETE_WARNING_BUTTON' => 'Si, bórralo!',
            'WIDGET_MANUAL_NOT_FOUND' => 'No hay manual disponible para su idioma',
            'WIDGET_FAQS_NOT_FOUND' => 'No hay preguntas frecuentes disponibles para su idioma',
            'DIMENSION_DASHBOARD_TAB' => 'Dashboard',
            'DIMENSION_DASHBOARD_TITLE_EVOLUTION' => 'Evolución',
            'DIMENSION_DASHBOARD_TITLE_ATTRIBUTES' => 'Atributos',
            'DIMENSION_DASHBOARD_TITLE_PERFORMANCE' => 'Performance',
            'DIMENSION_DASHBOARD_TITLE_CROSSINGS' => 'Cruces',
            'DIMENSION_DASHBOARD_TITLE_LOW_LEVEL' => 'Nivel Inferior',
            'DIMENSION_DASHBOARD_TITLE_STORE_RANKING' => 'Ranking centro de at.',
            'DIMENSION_DASHBOARD_TITLE_NPS_EXPLANATION' => 'Definición de NPS',
            'DIMENSION_DASHBOARD_VS_TM' => 'Vs. Anterior',
            'DIMENSION_DASHBOARD_PROM_COUNTRY_TM' => 'País',
            'DIMENSION_DASHBOARD_MCD_TOP' => 'GR TOP',
            'DIMENSION_RANKING_TAB' => 'Ranking',
            'DIMENSION_RANKING_TITLE_RANKING' => 'Ranking',
            'DIMENSION_RANKING_TITLE_ATTRIBUTES' => 'Atributos',
            'DIMENSION_COMMENTS_TAB' => 'Comentarios',
            'DIMENSION_DASHBOARD_NPS_EXPLANATION' =>
                '<p>En una escala de recomendación de la sucursal los clientes son clasificados en tres grupos:</p>
                <ul>
                <li>Los que responden 9 ó 10 puntos: promotores</li>
                <li>Los que asignan 7 u 8 puntos: pasivos</li>
                <li>Los que otorgan 6 puntos o menos: detractores</li>
                </ul>
                <p>Para obtener el NPS (Net Promoter Score) se restan los Detractores a los Promotores y se obtiene un porcentaje.<br>
                El índice NPS puede tener como mínimo -100 (todos los clientes son detractores) o +100 como máximo (todos son promotores).<br>
                Un NPS superior a 0 es bueno (al menos el saldo de promotores es positivo) y un NPS de 50 se considera excelente.</p>',
            'HISTORY_TAB_DIMENSION' => 'Dimensiones',
            'HISTORY_TAB_OWNER' => 'Propios - Franquiciados',
            'HISTORY_TAB_REGION' => 'Región',
            'HISTORY_TITLE_HISTORIC' => 'Histórico',
            'HISTORY_TITLE_DIMENSION' => 'Dimensiones',
            'HISTORY_TITLE_REGIONS' => 'Regiones',
            'HISTORY_TITLE_OWNER' => 'Propietario',
            'HISTORY_TITLE_HOURS' => 'Horarios',
            'HISTORY_TITLE_DAYS' => 'Días',
            'HISTORY_TITLE_COMPETENCE' => 'Competencia',
            'CASES_TITLE_CASE_HISTORY' => 'Historial Casos',
            'OPERATIONS_TITLE_HIERARCHY' => 'Estructura Operaciones',
            'COMPETENCE_TITLE_EVOLUTION' => 'Evolución',
            'COMPETENCE_TITLE_METRICS' => 'Competidores',
            'COMPETENCE_TITLE_COMPETENCE' => 'Competencia',
            'COMPETENCE_TITLE_CROSSINGS' => 'Satisfacción / Penetración de competencia',
            'COMPETENCE_TITLE_PENETRATION' => 'Penetración',
            'COMPETENCE_TITLE_SATISFACTION' => 'Satisfacción',
            'COMPETENCE_TITLE_COMPETITORS' => 'Competidores',
            'COMMENT_SEARCH' => 'Buscar',
            'COMMENT_SEARCH_PLACEHOLDER' => 'Buscar comentario',
            'COMMENT_DATE' => 'Fecha',
            'COMMENT_GENDER' => 'Género',
            'COMMENT_AGE' => 'Edad',
            'COMMENT_SEGMENTS' => 'Segmentos',
            'COMMENT_DAYS' => 'Días',
            'COMMENT_HOURS' => 'Horarios',
            'COMMENT_CATEGORY' => 'Categoria',
            'COMMENT_TITLE_THEME_CLOUD' => 'Unidades temáticas',
            'COMMENT_TITLE_WORD_CLOUD' => 'Nube términos',
            'COMMENT_TITLE_LIST_COMMENTS' => 'Comentarios',
            'REGION_NAME_LATAM' => 'Latinoamérica',
            'NO_DATA' => 'No hay datos disponibles',
            'COMMENT_SEARCH_RESULTS' => 'Se encontraron <%= results %> resultados'
        );
    }
}