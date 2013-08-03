
function __G__TRACKBACK__(errorMessage)
    CCLuaLog("----------------------------------------")
    CCLuaLog("LUA ERROR: " .. tostring(errorMessage) .. "\n")
    CCLuaLog(debug.traceback("", 2))
    CCLuaLog("----------------------------------------")
end

CCLuaLoadChunksFromZip("res/framework_precompiled.zip")

function main()
    require("config")
    require("framework.init")
    require("framework.client.init")

    CCFileUtils:sharedFileUtils():addSearchPath("res/")
    display.replaceScene(require("scenes.MainScene").new())
end

xpcall(main, __G__TRACKBACK__)
