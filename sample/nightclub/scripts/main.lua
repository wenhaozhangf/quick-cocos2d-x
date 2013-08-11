
function __G__TRACKBACK__(errorMessage)
    CCLuaLog("----------------------------------------")
    CCLuaLog("LUA ERROR: " .. tostring(errorMessage) .. "\n")
    CCLuaLog(debug.traceback("", 2))
    CCLuaLog("----------------------------------------")
end

-- CCLuaLoadChunksFromZip("res/framework_precompiled.zip")

function main()
    require("config")
    require("framework.init")

    CCFileUtils:sharedFileUtils():addSearchPath("res/")

    -- preload all musics
    for k, v in pairs(MUSIC) do
        audio.preloadMusic(v)
    end

    -- preload all effects
    for k, v in pairs(EFFECT) do
        audio.preloadEffect(v)
    end

    display.replaceScene(require("scenes.MainScene").new())
end

xpcall(main, __G__TRACKBACK__)
