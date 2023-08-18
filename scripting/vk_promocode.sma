#include <amxmodx>
#include <sqlx>
#define PLUGIN				"VK Promocode"
#define VERSION				"1.0"
#define AUTHOR				"uMk0"
#define MULTI_LANGUAGE(%0) fmt("%L", LANG_SERVER, %0)
enum _:cvars
{
    CVAR_SQL_HOST[128],
    CVAR_SQL_USER[64],
    CVAR_SQL_PASS[64],
    CVAR_SQL_DB[64],
    CVAR_SQL_TABLE_PREFIX[16],
    CVAR_SQL_CREATE_DB,
    CVAR_SQL_MAXFAIL,
    CVAR_LOG_DIR[32],
    CVAR_LOG_NAME[32],
}
enum _:sql_que_type
{
    SQL_INITDB,
    SQL_CHECK_PROMO,
    SQL_SET_PLAYER_PROMO,
    SQL_PLAYER_CONNECT
}

enum _:promo_use
{
    PLAYER_STEAMID[64],
    FLAGS = 0,
    TIME_END = 0
}
const QUERY_LENGTH = 1472;
new cvar[cvars],
    Handle:sql,
    bool:init_table = false,
    logsDir[64],
    dataDir[64],
    cnt_sqlfail,
    g_promoUse[MAX_PLAYERS + 1][promo_use],
    bool:gg_sql = false;

public plugin_init()
{
    register_plugin(PLUGIN, VERSION, AUTHOR);
    register_clcmd("vkp", "UsePromocode");
}
public plugin_precache()
{
    new szFileName[256];
    formatex(szFileName, charsmax(szFileName), "addons/amxmodx/data/lang/vk_promocode.txt");

    if (!file_exists(szFileName))
    {
        write_file(szFileName,
                   "[ru]^n\
					VKP_CVAR_VERSION = Версия плагина^^nНе редактировать эту переменную^n\
					VKP_CVAR_SQL_HOST = Хост MySQL^n\
					VKP_CVAR_SQL_USER = Пользователь MySQL^n\
					VKP_CVAR_SQL_PASS = Пароль пользователя MySQL^n\
					VKP_CVAR_SQL_DB = Имя БД^n\
					VKP_CVAR_SQL_PREFIX = Префикс таблицы^n\
					VKP_CVAR_SQL_MAXFAIL = Максимальное количество неудачных запросов^n\
					VKP_CVAR_LOG_DIR = Название папки с логами^n\
					VKP_CVAR_LOG_NAME = Название файла с логами^n\
					VKP_TAG = ^^3[^^4VKP^^3]^^1^n\
					VKP_USE_PROMO = Вы успешно активировали промокод, окончание активации %s^n\
					VKP_LOG_USE_PROMOCODE = %s активировал/а промокод <%s> с флагами <%s> дата покупки <%s> количество дней <%d> активация заканчивается <%s>^n\
					[en]^n\
					VKP_CVAR_VERSION = Plugin version^^nDon't edit this variable^n\
					VKP_CVAR_SQL_HOST = MySQL Host^n\
					VKP_CVAR_SQL_USER = MySQL User^n\
					VKP_CVAR_SQL_PASS = MySQL user password^n\
					VKP_CVAR_SQL_DB = Database name^n\
					VKP_CVAR_SQL_PREFIX = Table Prefix^n\
					VKP_CVAR_SQL_MAXFAIL = Maximum number of failed queries^n\
					VKP_CVAR_LOG_DIR = Name of the folder with logs^n\
					VKP_CVAR_LOG_NAME = Name of the log file^n\
					VKP_TAG = ^^3[^^4VKP^^3]^^1^n\
					VKP_USE_PROMO = You have successfully activated the promo code, activation end %s^n\
					VKP_LOG_USE_PROMOCODE = %s activated promo code <%s> with flags <%s> purchase date <%s> number of days <%d> activation ends <%s>^n\
					[ua]^n\
					VKP_CVAR_VERSION = Версія плагіна^^nНе редагувати цю змінну^n\
					VKP_CVAR_SQL_HOST = Хост MySQL^n\
					VKP_CVAR_SQL_USER = Користувач MySQL^n\
					VKP_CVAR_SQL_PASS = Пароль користувача MySQL^n\
					VKP_CVAR_SQL_DB = Ім'я БД^n\
					VKP_CVAR_SQL_PREFIX = Префікс таблиці^n\
					VKP_CVAR_SQL_MAXFAIL = Максимальна кількість невдалих запитів^n\
					VKP_CVAR_LOG_DIR = Назва папки з логами^n\
					VKP_CVAR_LOG_NAME = Назва файлу з логами^n\
					VKP_TAG = ^^3[^^4VKP^^3]^^1^n\
					VKP_USE_PROMO = Ви успішно активували промокод, закінчення активації %s^n\
					VKP_LOG_USE_PROMOCODE = %s активував промокод <%s> з прапорами <%s> дата купівлі <%s> кількість днів <%d> активація закінчується <%s>\
				");
    }
    register_dictionary("vk_promocode.txt");
    create_cvar("vkp_version", VERSION, FCVAR_SERVER | FCVAR_EXTDLL | FCVAR_UNLOGGED | FCVAR_SPONLY, MULTI_LANGUAGE("VKP_CVAR_VERSION"));
    bind_pcvar_string(create_cvar(
                          .name		   = "vkp_sql_host",
                          .string	   = "localhost",
                          .flags	   = FCVAR_UNLOGGED | FCVAR_PROTECTED,
                          .description = MULTI_LANGUAGE("VKP_CVAR_SQL_HOST")),
                      cvar[CVAR_SQL_HOST], charsmax(cvar[CVAR_SQL_HOST]));
    bind_pcvar_string(create_cvar(
                          .name		   = "vkp_sql_user",
                          .string	   = "root",
                          .flags	   = FCVAR_UNLOGGED | FCVAR_PROTECTED,
                          .description = MULTI_LANGUAGE("VKP_CVAR_SQL_USER")),
                      cvar[CVAR_SQL_USER], charsmax(cvar[CVAR_SQL_USER]));
    bind_pcvar_string(create_cvar(
                          .name		   = "vkp_sql_pass",
                          .string	   = "",
                          .flags	   = FCVAR_UNLOGGED | FCVAR_PROTECTED,
                          .description = MULTI_LANGUAGE("VKP_CVAR_SQL_PASS")),
                      cvar[CVAR_SQL_PASS], charsmax(cvar[CVAR_SQL_PASS]));
    bind_pcvar_string(create_cvar(
                          .name		   = "vkp_sql_name",
                          .string	   = "amxx",
                          .flags	   = FCVAR_UNLOGGED | FCVAR_PROTECTED,
                          .description = MULTI_LANGUAGE("VKP_CVAR_SQL_DB")),
                      cvar[CVAR_SQL_DB], charsmax(cvar[CVAR_SQL_DB]));
    bind_pcvar_string(create_cvar(
                          .name 	   = "vkp_sql_table_prefix",
                          .string 	   = "vkp",
                          .flags       = FCVAR_UNLOGGED | FCVAR_PROTECTED,
                          .description = MULTI_LANGUAGE("VKP_CVAR_SQL_PREFIX")),
                      cvar[CVAR_SQL_TABLE_PREFIX], charsmax(cvar[CVAR_SQL_TABLE_PREFIX]));
    bind_pcvar_num(create_cvar(
                          .name		   = "vkp_sql_maxfail",
                          .string	   = "10",
                          .flags	   = FCVAR_UNLOGGED | FCVAR_PROTECTED,
                          .description = MULTI_LANGUAGE("VKP_CVAR_SQL_MAXFAIL"),
                          .has_min	   = true,
                          .min_val	   = 0.0),
                      cvar[CVAR_SQL_MAXFAIL]);
    bind_pcvar_string(create_cvar(
                          .name		   = "vkp_log_dir",
                          .string	   = "vkp",
                          .flags	   = FCVAR_NONE,
                          .description = MULTI_LANGUAGE("VKP_CVAR_LOG_DIR")),
                      cvar[CVAR_LOG_DIR], charsmax(cvar[CVAR_LOG_DIR]));
    bind_pcvar_string(create_cvar(
                          .name		   = "vkp_log_name",
                          .string	   = "vkp_use",
                          .flags	   = FCVAR_NONE,
                          .description = MULTI_LANGUAGE("VKP_CVAR_LOG_NAME")),
                      cvar[CVAR_LOG_NAME], charsmax(cvar[CVAR_LOG_NAME]));

    AutoExecConfig();
}
forward OnConfigsExecuted();
public OnConfigsExecuted()
{
    get_localinfo("amxx_logs", logsDir, charsmax(logsDir));
    get_localinfo("amxx_datadir", dataDir, charsmax(dataDir));
    formatex(logsDir, charsmax(logsDir), "%s/%s", logsDir, cvar[CVAR_LOG_DIR]);
    if (!dir_exists(logsDir))
    {
        mkdir(logsDir);
    }
    if (!SQL_SetAffinity("mysql")) // Hi wopox3
    {
        new error_msg[128];
        formatex(error_msg, charsmax(error_msg), "failed to use mysql");
        set_fail_state(error_msg);
        return;
    }
    sql = SQL_MakeDbTuple(cvar[CVAR_SQL_HOST], cvar[CVAR_SQL_USER], cvar[CVAR_SQL_PASS], cvar[CVAR_SQL_DB]);
    SQL_SetCharset(sql, "utf8");
    new query[QUERY_LENGTH], sql_data[1];
    sql_data[0] = SQL_INITDB;
    formatex(query, charsmax(query), "\
                    					CREATE TABLE IF NOT EXISTS `%s_orders` (\
  										`id` int(11) NOT NULL AUTO_INCREMENT,\
  										`id_order` varchar(255) NOT NULL,\
  										`id_service` int(11) NOT NULL,\
  										`id_day` int(11) NOT NULL,\
  										`status` int(11) NOT NULL,\
  										`last_upd` int(11) NOT NULL,\
  										`peer_id` int(11) NOT NULL,\
  										PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8;\
										CREATE TABLE `%s_promo` (\
										`id` int(11) NOT NULL AUTO_INCREMENT,\
										`promocode` varchar(255) NOT NULL,\
										`time_pay` int(11) NOT NULL,\
										`time_activated` int(11) NOT NULL,\
										`time_end` int(11) NOT NULL,\
										`days` int(11) NOT NULL,\
										`steamid` varchar(255) NOT NULL,\
										`flags` varchar(255) NOT NULL,\
										`service_name` varchar(255) NOT NULL,\
                                        `id_vk` int(11) NOT NULL,\
										PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8;\
									", cvar[CVAR_SQL_TABLE_PREFIX], cvar[CVAR_SQL_TABLE_PREFIX]);
    SQL_ThreadQuery(sql, "SQL_Handler", query, sql_data, sizeof sql_data);
}
public finishInitDB()
{
    for (new i; i < 33; i++)
    {
        if (is_user_connected(i))
            connectPlayer(i);
    }
}
public client_putinserver(id)
{
    get_user_authid(id, g_promoUse[id][PLAYER_STEAMID], charsmax(g_promoUse[][PLAYER_STEAMID]));
    if (!init_table)
        return;
    connectPlayer(id);
}
public client_disconnected(id)
{
    g_promoUse[id][FLAGS] = 0;
    g_promoUse[id][TIME_END] = 0;
}
connectPlayer(id)
{
    // тут проверка привзяан ли промокод
    new query[QUERY_LENGTH], sql_data[2];
    sql_data[0] = SQL_PLAYER_CONNECT;
    sql_data[1] = id;
    formatex(query, charsmax(query), "\
                    SELECT * FROM `%s_promo` WHERE `steamid` LIKE '%s' AND `time_end` <= %d", cvar[CVAR_SQL_TABLE_PREFIX], g_promoUse[id][PLAYER_STEAMID], get_systime());

    SQL_ThreadQuery(sql, "SQL_Handler", query, sql_data, sizeof sql_data);
}
public UsePromocode(id)
{
    if (read_argc() < 2)
    {
        return;
    }
    new promocode[256]
    read_argv(1, promocode, charsmax(promocode));
    mysql_escape_string(promocode, charsmax(promocode));

    new query[QUERY_LENGTH], sql_data[2];
    sql_data[0] = SQL_CHECK_PROMO;
    sql_data[1] = id;
    formatex(query, charsmax(query), "\
                    SELECT * FROM `%s_promo` WHERE `promocode` LIKE '%s' AND `time_activated` = 0", cvar[CVAR_SQL_TABLE_PREFIX], promocode);

    SQL_ThreadQuery(sql, "SQL_Handler", query, sql_data, sizeof sql_data);

}
public SQL_Handler(failstate, Handle:sqlQue, err[], errNum, data[], dataSize)
{
    switch (failstate)
    {
        case TQUERY_CONNECT_FAILED:
        {
            logging(logsDir, cvar[CVAR_LOG_NAME], "SQL connection failed");
            logging(logsDir, cvar[CVAR_LOG_NAME], "[ %d ] %s", errNum, err);
            cnt_sqlfail++;
            if (cnt_sqlfail >= cvar[CVAR_SQL_MAXFAIL] && !gg_sql)
            {
                logging(logsDir, cvar[CVAR_LOG_NAME], "db query is disabled for this map")
                gg_sql = true
            }
            return PLUGIN_HANDLED
        }
        case TQUERY_QUERY_FAILED:
        {
            new lastQue[QUERY_LENGTH]
            SQL_GetQueryString(sqlQue, lastQue, charsmax(lastQue)) // find out the last SQL query
            logging(logsDir, cvar[CVAR_LOG_NAME], "SQL query failed");
            logging(logsDir, cvar[CVAR_LOG_NAME], "[ %d ] %s", errNum, err);
            logging(logsDir, cvar[CVAR_LOG_NAME], "[ SQL ] %s", lastQue);
            cnt_sqlfail++;
            if (cnt_sqlfail >= cvar[CVAR_SQL_MAXFAIL] && !gg_sql)
            {
                logging(logsDir, cvar[CVAR_LOG_NAME], "db query is disabled for this map")
                gg_sql = true
            }
            return PLUGIN_HANDLED
        }
    }
    switch (data[0])
    {
        case SQL_INITDB:
        {
            init_table = true;
            finishInitDB();
        }
        case SQL_PLAYER_CONNECT:
        {
            if (SQL_NumResults(sqlQue) > 0)
            {
                new flags_tmp[64], player_flags;
                //data[1] - id игрока
                for (new i = 0; i < SQL_NumResults(sqlQue); i++)
                {

                    SQL_ReadResult(sqlQue, 7, flags_tmp, charsmax(flags_tmp));
                    if (g_promoUse[data[1]][FLAGS] == 0)
                    {
                        g_promoUse[data[1]][FLAGS] = read_flags(flags_tmp);
                    }
                    else
                    {
                        g_promoUse[data[1]][FLAGS] = g_promoUse[data[1]][FLAGS] | read_flags(flags_tmp);
                    }
                    if (g_promoUse[data[1]][TIME_END] == 0)
                    {
                        g_promoUse[data[1]][TIME_END] = SQL_ReadResult(sqlQue, 4);
                    }
                    else
                    {
                        if (g_promoUse[data[1]][TIME_END] <= SQL_ReadResult(sqlQue, 4))
                        {
                            g_promoUse[data[1]][TIME_END] = SQL_ReadResult(sqlQue, 4);
                        }
                    }
                    SQL_NextRow(sqlQue);
                }
                player_flags = get_user_flags(data[1]);
                set_user_flags(data[1], player_flags | g_promoUse[data[1]][FLAGS]);
                remove_user_flags(data[1], read_flags("z"));
            }
        }
        case SQL_CHECK_PROMO:
        {
            if (SQL_NumResults(sqlQue) > 0)
            {
                //найден не используемый промокод
                new flags_tmp[64], player_flags, time_end = get_systime(), days, szTimeEnd[64], promocode[255], time_pay, szTimePay[64], player_name[32];
                get_user_name(data[1], player_name, charsmax(player_name))
                SQL_ReadResult(sqlQue, 7, flags_tmp, charsmax(flags_tmp));
                if (g_promoUse[data[1]][FLAGS] == 0)
                {
                    g_promoUse[data[1]][FLAGS] = read_flags(flags_tmp);
                }
                else
                {
                    g_promoUse[data[1]][FLAGS] = g_promoUse[data[1]][FLAGS] | read_flags(flags_tmp);
                }
                days = SQL_ReadResult(sqlQue, 5);
                time_end = time_end + (days * 86400);

                if (g_promoUse[data[1]][TIME_END] == 0)
                {
                    g_promoUse[data[1]][TIME_END] = time_end;
                }
                else
                {
                    if (g_promoUse[data[1]][TIME_END] <= time_end)
                    {
                        g_promoUse[data[1]][TIME_END] = time_end;
                    }
                }
                time_pay = SQL_ReadResult(sqlQue, 2);
                format_time(szTimePay, charsmax(szTimePay), "%d.%m.%Y", time_pay);
                SQL_ReadResult(sqlQue, 1, promocode, charsmax(promocode));
                player_flags = get_user_flags(data[1]);
                set_user_flags(data[1], player_flags | g_promoUse[data[1]][FLAGS]);
                remove_user_flags(data[1], read_flags("z"));
                format_time(szTimeEnd, charsmax(szTimeEnd), "%d.%m.%Y", time_end);
                client_print_color(data[1], print_team_default, "%L %L", LANG_SERVER, "VKP_TAG", LANG_SERVER, "VKP_USE_PROMO", szTimeEnd);

                new query[QUERY_LENGTH];

                formatex(query, charsmax(query), "\
				UPDATE `%s_promo` SET `time_activated` = '%d', `time_end` = '%d', `steamid` = '%s' WHERE `id` = %d;", cvar[CVAR_SQL_TABLE_PREFIX], get_systime(), time_end, g_promoUse[data[1]][PLAYER_STEAMID], SQL_ReadResult(sqlQue, 0));
                SQL_ThreadQuery(sql, "SQL_Handler", query);
                logging(logsDir, cvar[CVAR_LOG_NAME], "%L", LANG_SERVER, "VKP_LOG_USE_PROMOCODE", player_name, promocode, flags_tmp, szTimePay, days, szTimeEnd);
            }
        }
    }
    return PLUGIN_HANDLED
}
stock logging(const sLogsDir[], const sFileName[], const sMessage[], any:...)
{
    new sFmtMsg[512], sLogFile[96], sRecordTime[32], iFileID;
    vformat(sFmtMsg, charsmax(sFmtMsg), sMessage, 4);

    new sFileTime[32];
    get_time("%d.%m.%Y", sFileTime, charsmax(sFileTime));
    formatex(sLogFile, charsmax(sLogFile), "%s/%s_%s.log", sLogsDir, sFileName, sFileTime);

    iFileID = fopen(sLogFile, "at");
    get_time("%d.%m.%Y - %H:%M:%S", sRecordTime, charsmax(sRecordTime));
    fprintf(iFileID, "^"%s^" %s^n", sRecordTime, sFmtMsg);
    fclose(iFileID);
}
stock mysql_escape_string(dest[], len)
{
    replace_all(dest, len, "\\", "\\\\");
    replace_all(dest, len, "\0", "\\0");
    replace_all(dest, len, "\n", "\\n");
    replace_all(dest, len, "\r", "\\r");
    replace_all(dest, len, "\x1a", "\Z");
    replace_all(dest, len, "'", "''");
    replace_all(dest, len, "^"","^"^"");
}