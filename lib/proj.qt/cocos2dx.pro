#-------------------------------------------------
#
# Project created by QtCreator 2013-10-16T03:24:52
#
#-------------------------------------------------

QT += widgets opengl

TARGET = cocos2dx
TEMPLATE = lib

CONFIG += static

DEFINES += CC_TARGET_QT

macx {
    QMAKE_CC    = clang++
    QMAKE_CXX   = clang++

    DISABLED_WARNINGS = -Wno-ignored-qualifiers -Wno-unused-parameter -Wno-psabi
    QMAKE_CFLAGS += $${DISABLED_WARNINGS}
    QMAKE_CXXFLAGS += $${DISABLED_WARNINGS} -Wno-reorder

    DEFINES += USE_FILE32API

    # You may need to change this include directory
    INCLUDEPATH += \
            ../cocos2d-x/cocos2dx/platform/third_party/mac/webp \
            /usr/include \
            /usr/local/include \
            /opt/local/include

    COCOS2DX_SYSTEM_LIBS += -L../cocos2d-x/cocos2dx/platform/third_party/mac/libraries -lwebp
    COCOS2DX_SYSTEM_LIBS += -L../cocos2d-x/external/libwebsockets/mac/include/lib -lwebsockets
    COCOS2DX_SYSTEM_LIBS += -L/usr/local/lib -ljpeg -ltiff -lpng -lfontconfig -lfreetype -lz
    COCOS2DX_SYSTEM_LIBS += -L/opt/local/lib -ljpeg -ltiff -lpng -lfontconfig -lfreetype -lz
}

LIBS += $${COCOS2DX_SYSTEM_LIBS}

include(cocos2dx.pri)
include(CocosDenshion.pri)
include(extensions.pri)

